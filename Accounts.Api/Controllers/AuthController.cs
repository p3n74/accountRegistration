using Accounts.Api.DTOs;
using Accounts.Data.Models;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using System.Linq;

namespace Accounts.Api.Controllers;

[ApiController]
[Route("api/[controller]")]
public class AuthController : ControllerBase
{
    private readonly Accounts.Data.Models.AccountsDbContext _db;
    private readonly Accounts.Api.Services.EmailService _email;
    private readonly ILogger<AuthController> _logger;

    public AuthController(Accounts.Data.Models.AccountsDbContext db, Accounts.Api.Services.EmailService email, ILogger<AuthController> logger)
    {
        _db = db;
        _email = email;
        _logger = logger;
    }

    [HttpPost("register")]
    public async Task<ActionResult<AuthResponse>> Register(RegisterRequest req)
    {
        // Check if email already exists
        var exists = await _db.UserCredentials.AnyAsync(u => u.Email == req.Email);
        if (exists) return Conflict("Email already exists");

        // Check if this email belongs to an existing student
        var existingStudent = await _db.ExistingStudentInfos
            .AsNoTracking()
            .FirstOrDefaultAsync(s => s.Email == req.Email);

        // If an existing student record is found, override the provided names/program
        var fname = existingStudent?.FirstName ?? req.FirstName;
        var mname = existingStudent?.MiddleName ?? req.MiddleName;
        var lname = existingStudent?.LastName ?? req.LastName;
        var programId = existingStudent?.ProgramId ?? req.ProgramId ?? 0;

        var fullName = string.Join(" ", new[] { fname, mname, lname }.Where(s => !string.IsNullOrWhiteSpace(s)));

        // Generate 64-char verification token
        var verificationToken = Guid.NewGuid().ToString("N") + Guid.NewGuid().ToString("N");

        var user = new UserCredentials
        {
            Uid = Guid.NewGuid().ToString(),
            Email = req.Email,
            Fname = fname,
            Mname = mname,
            Lname = lname,
            Fullname = fullName,
            Password = HashPassword(req.Password),
            Currboundtoken = verificationToken,
            Emailverified = false,
            IsStudent = existingStudent != null,
            ProgramId = programId,
            UserLevel = 0,
            Attendedevents = "[]",
            Creationtime = DateTime.UtcNow
        };

        _db.UserCredentials.Add(user);
        await _db.SaveChangesAsync();

        await _email.SendVerificationEmailAsync(user.Email, verificationToken);

        var token = GenerateDummyToken(user);
        return new AuthResponse(user.Uid, user.Email, user.UserLevel, token);
    }

    [HttpPost("login")]
    public async Task<ActionResult<AuthResponse>> Login(LoginRequest req)
    {
        var user = await _db.UserCredentials.FirstOrDefaultAsync(u => u.Email == req.Email);
        if (user == null) return Unauthorized("Invalid credentials");

        if (!VerifyPassword(req.Password, user.Password))
            return Unauthorized("Invalid credentials");

        if (user.Emailverified != true)
            return Unauthorized("Email not verified");

        var token = GenerateDummyToken(user);
        return new AuthResponse(user.Uid, user.Email, user.UserLevel, token);
    }

    [HttpPost("check-existing-student")]
    public async Task<IActionResult> CheckExistingStudent([FromBody] CheckStudentRequest request)
    {
        try
        {
            if (string.IsNullOrEmpty(request.Email))
            {
                return BadRequest(new { exists = false, message = "Email is required" });
            }

            var normalizedEmail = request.Email.ToLower().Trim();

            // Check if this email exists in the existing student info table
            var existingStudent = await _db.ExistingStudentInfos
                .FirstOrDefaultAsync(s => s.Email == normalizedEmail);

            if (existingStudent != null)
            {
                return Ok(new
                {
                    exists = true,
                    data = new
                    {
                        firstName = existingStudent.FirstName,
                        middleName = existingStudent.MiddleName,
                        lastName = existingStudent.LastName,
                        programId = existingStudent.ProgramId,
                        programName = "Program" // Simplified for now
                    }
                });
            }

            return Ok(new { exists = false });
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error checking existing student for email: {Email}", request.Email);
            return StatusCode(500, new { exists = false, message = "An error occurred" });
        }
    }

    /* -------- Helpers -------- */
    private static string HashPassword(string plain)
        => BCrypt.Net.BCrypt.HashPassword(plain);

    private static bool VerifyPassword(string plain, string hashed)
        => BCrypt.Net.BCrypt.Verify(plain, hashed);

    private static string GenerateDummyToken(UserCredentials user)
        => Convert.ToBase64String(Guid.NewGuid().ToByteArray());
}

public class CheckStudentRequest
{
    public string Email { get; set; } = string.Empty;
} 
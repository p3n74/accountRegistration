using Accounts.Api.DTOs;
using Accounts.Data;
using Accounts.Data.Models;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using System.Security.Cryptography;
using System.Text;

namespace Accounts.Api.Controllers;

[ApiController]
[Route("api/[controller]")]
public class AuthController : ControllerBase
{
    private readonly AccountsDbContext _db;

    public AuthController(AccountsDbContext db)
    {
        _db = db;
    }

    [HttpPost("register")]
    public async Task<ActionResult<AuthResponse>> Register(RegisterRequest req)
    {
        var exists = await _db.UserCredentials.AnyAsync(u => u.Email == req.Email);
        if (exists) return Conflict("Email already exists");

        var user = new UserCredentials
        {
            Email = req.Email,
            PasswordHash = HashPassword(req.Password),
            IsStudent = req.IsStudent,
            UserLevel = 0
        };
        _db.UserCredentials.Add(user);
        await _db.SaveChangesAsync();

        var token = GenerateDummyToken(user);
        return new AuthResponse(user.Uid, user.Email, user.UserLevel, token);
    }

    [HttpPost("login")]
    public async Task<ActionResult<AuthResponse>> Login(LoginRequest req)
    {
        var user = await _db.UserCredentials.FirstOrDefaultAsync(u => u.Email == req.Email);
        if (user == null) return Unauthorized();
        if (user.PasswordHash != HashPassword(req.Password)) return Unauthorized();

        var token = GenerateDummyToken(user);
        return new AuthResponse(user.Uid, user.Email, user.UserLevel, token);
    }

    /* -------- Helpers (placeholder, replace with JWT etc.) -------- */
    private static string HashPassword(string plain)
    {
        using var sha = SHA256.Create();
        var bytes = sha.ComputeHash(Encoding.UTF8.GetBytes(plain));
        return Convert.ToHexString(bytes);
    }

    private static string GenerateDummyToken(UserCredentials user) => Convert.ToBase64String(Guid.NewGuid().ToByteArray());
} 
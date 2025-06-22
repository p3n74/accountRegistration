using Accounts.Api.DTOs;
using Accounts.Data.Models;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.RazorPages;
using Microsoft.EntityFrameworkCore;
using System.ComponentModel.DataAnnotations;
using System.Collections.Generic;
using System.Linq;
using Accounts.Api.Services;
using BCrypt.Net;

namespace Accounts.Api.Pages.Auth;

public class RegisterModel : PageModel
{
    private readonly AccountsDbContext _context;
    private readonly EmailService _emailService;
    private readonly ILogger<RegisterModel> _logger;

    public RegisterModel(AccountsDbContext context, EmailService emailService, ILogger<RegisterModel> logger)
    {
        _context = context;
        _emailService = emailService;
        _logger = logger;
    }

    [BindProperty]
    [Required(ErrorMessage = "Email is required")]
    [EmailAddress(ErrorMessage = "Please enter a valid email address")]
    public string Email { get; set; } = string.Empty;

    [BindProperty]
    [Required(ErrorMessage = "First name is required")]
    [StringLength(50, ErrorMessage = "First name cannot exceed 50 characters")]
    public string FirstName { get; set; } = string.Empty;

    [BindProperty]
    [StringLength(50, ErrorMessage = "Middle name cannot exceed 50 characters")]
    public string? MiddleName { get; set; }

    [BindProperty]
    [Required(ErrorMessage = "Last name is required")]
    [StringLength(50, ErrorMessage = "Last name cannot exceed 50 characters")]
    public string LastName { get; set; } = string.Empty;

    [BindProperty]
    [Required(ErrorMessage = "Password is required")]
    [StringLength(100, MinimumLength = 8, ErrorMessage = "Password must be at least 8 characters long")]
    [DataType(DataType.Password)]
    public string Password { get; set; } = string.Empty;

    [BindProperty]
    [Required(ErrorMessage = "Please confirm your password")]
    [Compare("Password", ErrorMessage = "Passwords do not match")]
    [DataType(DataType.Password)]
    public string ConfirmPassword { get; set; } = string.Empty;

    [BindProperty]
    public int? ProgramId { get; set; }

    public string? ErrorMessage { get; set; }
    public string? SuccessMessage { get; set; }
    public List<ProgramInfo> Programs { get; set; } = new();

    public async Task<IActionResult> OnGetAsync()
    {
        // If already logged in, redirect to dashboard
        if (HttpContext.Session.GetString("uid") != null)
        {
            return RedirectToPage("/Dashboard/Index");
        }

        await LoadPrograms();
        return Page();
    }

    public async Task<IActionResult> OnPostAsync()
    {
        // If already logged in, redirect to dashboard
        if (HttpContext.Session.GetString("uid") != null)
        {
            return RedirectToPage("/Dashboard/Index");
        }

        await LoadPrograms();

        if (!ModelState.IsValid)
        {
            return Page();
        }

        try
        {
            var normalizedEmail = Email.ToLower().Trim();

            // Check if user already exists
            var existingUser = await _context.UserCredentials
                .FirstOrDefaultAsync(u => u.Email == normalizedEmail);

            if (existingUser != null)
            {
                ErrorMessage = "An account with this email already exists";
                _logger.LogWarning("Registration attempt with existing email: {Email}", Email);
                return Page();
            }

            // Check if this is an existing student
            var existingStudent = await _context.ExistingStudentInfos
                .FirstOrDefaultAsync(s => s.Email == normalizedEmail);

            bool isStudent = false;
            if (existingStudent != null)
            {
                // Override user input with existing student data
                FirstName = existingStudent.FirstName;
                MiddleName = existingStudent.MiddleName;
                LastName = existingStudent.LastName;
                ProgramId = existingStudent.ProgramId;
                isStudent = true;
            }

            // Create new user
            var userId = Guid.NewGuid().ToString();
            var verificationToken = Guid.NewGuid().ToString();
            var hashedPassword = BCrypt.Net.BCrypt.HashPassword(Password);

            var user = new UserCredentials
            {
                Uid = userId,
                Fname = FirstName,
                Mname = MiddleName,
                Lname = LastName,
                Fullname = $"{FirstName} {MiddleName} {LastName}".Trim(),
                Email = normalizedEmail,
                Password = hashedPassword,
                Currboundtoken = verificationToken,
                Emailverified = false,
                IsStudent = isStudent,
                ProgramId = ProgramId ?? 0,
                Creationtime = DateTime.UtcNow,
                UserLevel = 1 // Default user level
            };

            _context.UserCredentials.Add(user);
            await _context.SaveChangesAsync();

            // Send verification email
            await _emailService.SendVerificationEmailAsync(normalizedEmail, verificationToken);

            SuccessMessage = "Registration successful! Please check your email for a verification link before logging in.";
            _logger.LogInformation("User registered successfully: {Email}", Email);

            // Clear form fields after successful registration
            Email = string.Empty;
            FirstName = string.Empty;
            MiddleName = string.Empty;
            LastName = string.Empty;
            Password = string.Empty;
            ConfirmPassword = string.Empty;
            ProgramId = null;

            return Page();
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error during registration for email: {Email}", Email);
            ErrorMessage = "An error occurred during registration. Please try again.";
            return Page();
        }
    }

            private async Task LoadPrograms()
        {
            Programs = await _context.ProgramLists
                .OrderBy(p => p.ProgramName)
                .Select(p => new ProgramInfo
                {
                    ProgramId = p.ProgramId,
                    ProgramName = p.ProgramName,
                    DepartmentName = "General", // Simplified for now
                    SchoolName = "University of San Carlos"
                })
                .ToListAsync();
        }

    public class ProgramInfo
    {
        public int ProgramId { get; set; }
        public string ProgramName { get; set; } = string.Empty;
        public string DepartmentName { get; set; } = string.Empty;
        public string SchoolName { get; set; } = string.Empty;
    }
} 
using Accounts.Data.Models;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.RazorPages;
using Microsoft.EntityFrameworkCore;
using System.ComponentModel.DataAnnotations;
using Microsoft.AspNetCore.Http;
using BCrypt.Net;

namespace Accounts.Api.Pages.Auth;

public class LoginModel : PageModel
{
    private readonly AccountsDbContext _context;
    private readonly ILogger<LoginModel> _logger;

    public LoginModel(AccountsDbContext context, ILogger<LoginModel> logger)
    {
        _context = context;
        _logger = logger;
    }

    [BindProperty]
    [Required(ErrorMessage = "Email is required")]
    [EmailAddress(ErrorMessage = "Please enter a valid email address")]
    public string Email { get; set; } = string.Empty;

    [BindProperty]
    [Required(ErrorMessage = "Password is required")]
    [DataType(DataType.Password)]
    public string Password { get; set; } = string.Empty;

    [BindProperty]
    public bool RememberMe { get; set; }

    public string? ErrorMessage { get; set; }

    public async Task<IActionResult> OnGetAsync()
    {
        // If already logged in, redirect to dashboard
        if (HttpContext.Session.GetString("uid") != null)
        {
            return RedirectToPage("/Dashboard/Index");
        }

        return Page();
    }

    public async Task<IActionResult> OnPostAsync()
    {
        // If already logged in, redirect to dashboard
        if (HttpContext.Session.GetString("uid") != null)
        {
            return RedirectToPage("/Dashboard/Index");
        }

        if (!ModelState.IsValid)
        {
            return Page();
        }

        try
        {
            // Find user by email
            var user = await _context.UserCredentials
                .FirstOrDefaultAsync(u => u.Email == Email.ToLower().Trim());

            if (user == null)
            {
                ErrorMessage = "Email not registered";
                _logger.LogWarning("Login attempt with unregistered email: {Email}", Email);
                return Page();
            }

                            // Check if email is verified
                if (user.Emailverified != true)
                {
                    ErrorMessage = "Email not verified. Please check your inbox for the verification link.";
                    _logger.LogWarning("Login attempt with unverified email: {Email}", Email);
                    return Page();
                }

                // Verify password
                if (!BCrypt.Net.BCrypt.Verify(Password, user.Password))
                {
                    ErrorMessage = "Incorrect password";
                    _logger.LogWarning("Failed login attempt for user: {Email}", Email);
                    return Page();
                }

                // Successful login - create session
                HttpContext.Session.SetString("uid", user.Uid);
                HttpContext.Session.SetString("email", user.Email);
                HttpContext.Session.SetString("fname", user.Fname ?? "");
                HttpContext.Session.SetString("lname", user.Lname ?? "");
                HttpContext.Session.SetString("last_activity", DateTimeOffset.UtcNow.ToUnixTimeSeconds().ToString());

                // Log successful login
                _logger.LogInformation("Successful login for user: {Email}", Email);

                // Set success message
                TempData["SuccessMessage"] = $"Welcome back, {user.Fname}!";

            // Redirect to dashboard
            return RedirectToPage("/Dashboard/Index");
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error during login attempt for email: {Email}", Email);
            ErrorMessage = "An error occurred during login. Please try again.";
            return Page();
        }
    }
} 
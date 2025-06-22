using Accounts.Data.Models;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.RazorPages;
using Microsoft.EntityFrameworkCore;
using System.ComponentModel.DataAnnotations;

namespace Accounts.Api.Pages.Auth;

public class ResetRequestModel : PageModel
{
    private readonly AccountsDbContext _db;
    private readonly Accounts.Api.Services.EmailService _email;

    public ResetRequestModel(AccountsDbContext db, Accounts.Api.Services.EmailService email)
    {
        _db = db;
        _email = email;
    }

    [BindProperty]
    public EmailInput Input { get; set; } = new();

    public void OnGet() 
    {
        // Clear any existing messages
        TempData.Clear();
    }

    public async Task<IActionResult> OnPostAsync()
    {
        if (!ModelState.IsValid)
        {
            return Page();
        }

        try
        {
            var user = await _db.UserCredentials.FirstOrDefaultAsync(u => u.Email == Input.Email.ToLowerInvariant().Trim());
            if (user == null)
            {
                // Don't reveal if email exists or not for security
                TempData["SuccessMessage"] = "If an account with that email exists, we've sent a password reset link to it.";
                return Page();
            }

            if (user.Emailverified != true)
            {
                TempData["ErrorMessage"] = "Please verify your email address first before requesting a password reset.";
                return Page();
            }

            // Generate secure 64-character token
            var resetToken = GenerateSecureToken();
            user.PasswordResetToken = resetToken;
            user.PasswordResetExpiry = DateTime.UtcNow.AddHours(1);
            await _db.SaveChangesAsync();

            // Send reset email
            await _email.SendPasswordResetEmailAsync(user.Email, resetToken);

            TempData["SuccessMessage"] = "If an account with that email exists, we've sent a password reset link to it. Please check your email.";
            return Page();
        }
        catch (Exception ex)
        {
            TempData["ErrorMessage"] = "An error occurred while processing your request. Please try again.";
            return Page();
        }
    }

    private static string GenerateSecureToken() => 
        Guid.NewGuid().ToString("N") + Guid.NewGuid().ToString("N");

    public record EmailInput
    {
        [Required(ErrorMessage = "Email is required")]
        [EmailAddress(ErrorMessage = "Please enter a valid email address")]
        [Display(Name = "Email Address")]
        public string Email { get; set; } = string.Empty;
    }
} 
using Accounts.Data.Models;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.RazorPages;
using Microsoft.EntityFrameworkCore;
using System.ComponentModel.DataAnnotations;
using BCrypt.Net;

namespace Accounts.Api.Pages.Auth;

public class ResetPasswordModel : PageModel
{
    private readonly AccountsDbContext _db;

    public ResetPasswordModel(AccountsDbContext db)
    {
        _db = db;
    }

    [BindProperty]
    public PasswordInput Input { get; set; } = new();

    [FromRoute(Name = "token")] 
    public string? Token { get; set; }

    public async Task<IActionResult> OnGetAsync()
    {
        if (string.IsNullOrWhiteSpace(Token))
        {
            TempData["ErrorMessage"] = "Invalid or missing reset token.";
            return Page();
        }

        try
        {
            var user = await _db.UserCredentials.FirstOrDefaultAsync(u => 
                u.PasswordResetToken == Token && 
                u.PasswordResetExpiry > DateTime.UtcNow);
                
            if (user == null)
            {
                TempData["ErrorMessage"] = "Invalid or expired reset token. Please request a new password reset.";
                return Page();
            }

            return Page();
        }
        catch (Exception ex)
        {
            TempData["ErrorMessage"] = "An error occurred while validating your reset token. Please try again.";
            return Page();
        }
    }

    public async Task<IActionResult> OnPostAsync()
    {
        if (!ModelState.IsValid)
        {
            return Page();
        }

        if (string.IsNullOrWhiteSpace(Token))
        {
            TempData["ErrorMessage"] = "Invalid or missing reset token.";
            return Page();
        }

        try
        {
            var user = await _db.UserCredentials.FirstOrDefaultAsync(u => 
                u.PasswordResetToken == Token && 
                u.PasswordResetExpiry > DateTime.UtcNow);
                
            if (user == null)
            {
                TempData["ErrorMessage"] = "Invalid or expired reset token. Please request a new password reset.";
                return Page();
            }

            // Update password and clear reset token
            user.Password = HashPassword(Input.Password);
            user.PasswordResetToken = null;
            user.PasswordResetExpiry = null;
            await _db.SaveChangesAsync();

            TempData["SuccessMessage"] = "Your password has been reset successfully. Please log in with your new password.";
            return RedirectToPage("Login", new { reset = true });
        }
        catch (Exception ex)
        {
            TempData["ErrorMessage"] = "An error occurred while resetting your password. Please try again.";
            return Page();
        }
    }

    private static string HashPassword(string plain) => BCrypt.Net.BCrypt.HashPassword(plain, 12);

    public record PasswordInput
    {
        [Required(ErrorMessage = "Password is required")]
        [DataType(DataType.Password)]
        [StringLength(100, MinimumLength = 8, ErrorMessage = "Password must be at least 8 characters long")]
        [Display(Name = "New Password")]
        public string Password { get; set; } = string.Empty;

        [Required(ErrorMessage = "Please confirm your password")]
        [DataType(DataType.Password)]
        [Display(Name = "Confirm New Password")]
        [Compare(nameof(Password), ErrorMessage = "Passwords do not match")]
        public string ConfirmPassword { get; set; } = string.Empty;
    }
} 
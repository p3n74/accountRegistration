using Accounts.Data.Models;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.RazorPages;
using Microsoft.EntityFrameworkCore;
using System.ComponentModel.DataAnnotations;
using System.Security.Cryptography;
using System.Text;

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

    public string? Error { get; set; }
    public string? Success { get; set; }

    [FromRoute(Name = "token")] public string? Token { get; set; }

    public async Task<IActionResult> OnGetAsync()
    {
        if (string.IsNullOrWhiteSpace(Token))
        {
            Error = "Invalid or expired reset token.";
            return Page();
        }

        var user = await _db.UserCredentials.FirstOrDefaultAsync(u => u.PasswordResetToken == Token && u.PasswordResetExpiry > DateTime.UtcNow);
        if (user == null)
        {
            Error = "Invalid or expired reset token.";
        }
        return Page();
    }

    public async Task<IActionResult> OnPostAsync()
    {
        if (!ModelState.IsValid)
            return Page();

        if (string.IsNullOrWhiteSpace(Token))
        {
            Error = "Invalid or expired reset token.";
            return Page();
        }

        var user = await _db.UserCredentials.FirstOrDefaultAsync(u => u.PasswordResetToken == Token && u.PasswordResetExpiry > DateTime.UtcNow);
        if (user == null)
        {
            Error = "Invalid or expired reset token.";
            return Page();
        }

        user.Password = HashPassword(Input.Password);
        user.PasswordResetToken = null;
        user.PasswordResetExpiry = null;
        await _db.SaveChangesAsync();

        Success = "Your password has been reset. Please log in.";
        return RedirectToPage("Login", new { reset = true });
    }

    private static string HashPassword(string plain)
    {
        using var sha = SHA256.Create();
        var bytes = sha.ComputeHash(Encoding.UTF8.GetBytes(plain));
        return Convert.ToHexString(bytes);
    }

    public class PasswordInput
    {
        [Required]
        [DataType(DataType.Password)]
        public string Password { get; set; } = string.Empty;

        [Required]
        [DataType(DataType.Password)]
        [Display(Name = "Confirm Password")]
        [Compare(nameof(Password))]
        public string ConfirmPassword { get; set; } = string.Empty;
    }
} 
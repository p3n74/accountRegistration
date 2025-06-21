using Accounts.Data.Models;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.RazorPages;
using Microsoft.EntityFrameworkCore;
using System.ComponentModel.DataAnnotations;
using System.Security.Cryptography;
using System.Text;

namespace Accounts.Api.Pages.Auth;

public class ResetRequestModel : PageModel
{
    private readonly AccountsDbContext _db;

    public ResetRequestModel(AccountsDbContext db)
    {
        _db = db;
    }

    [BindProperty]
    public EmailInput Input { get; set; } = new();

    public string? Error { get; set; }
    public string? Success { get; set; }

    public void OnGet() { }

    public async Task<IActionResult> OnPostAsync()
    {
        if (!ModelState.IsValid)
            return Page();

        var user = await _db.UserCredentials.FirstOrDefaultAsync(u => u.Email == Input.Email);
        if (user == null)
        {
            Error = "We couldn't find an account with that email.";
            return Page();
        }

        // Generate token (simple placeholder)
        user.PasswordResetToken = Guid.NewGuid().ToString("N");
        user.PasswordResetExpiry = DateTime.UtcNow.AddHours(1);
        await _db.SaveChangesAsync();

        // TODO: Send email (out of scope)
        Success = "We've emailed you a link to reset your password.";
        return Page();
    }

    public class EmailInput
    {
        [Required, EmailAddress]
        public string Email { get; set; } = string.Empty;
    }
} 
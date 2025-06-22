using Accounts.Data.Models;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.RazorPages;
using Microsoft.EntityFrameworkCore;

namespace Accounts.Api.Pages.Auth;

[IgnoreAntiforgeryToken]
public class VerifyModel : PageModel
{
    private readonly AccountsDbContext _db;
    
    public string? Message { get; private set; }
    public bool Success { get; private set; }

    public VerifyModel(AccountsDbContext db)
    {
        _db = db;
    }

    public async Task OnGetAsync(string? token)
    {
        if (string.IsNullOrWhiteSpace(token))
        {
            Success = false;
            Message = "Missing or invalid verification token.";
            return;
        }

        try
        {
            var user = await _db.UserCredentials.FirstOrDefaultAsync(u => u.Currboundtoken == token);
            if (user == null)
            {
                Success = false;
                Message = "Invalid or expired verification token. Please request a new verification email.";
                return;
            }

            if (user.Emailverified == true)
            {
                Success = true;
                Message = "Your email is already verified. You can log in to your account.";
                return;
            }

            // Verify the email
            user.Emailverified = true;
            user.Currboundtoken = null; // Clear the token after use
            await _db.SaveChangesAsync();

            Success = true;
            Message = "Email verified successfully! You can now log in to your account.";
        }
        catch (Exception ex)
        {
            Success = false;
            Message = "An error occurred while verifying your email. Please try again or contact support.";
        }
    }
} 
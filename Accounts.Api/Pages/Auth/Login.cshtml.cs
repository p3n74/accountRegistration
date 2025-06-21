using Accounts.Data.Models;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.RazorPages;
using Microsoft.EntityFrameworkCore;
using System.ComponentModel.DataAnnotations;
using System.Security.Cryptography;
using System.Text;

namespace Accounts.Api.Pages.Auth;

public class LoginModel : PageModel
{
    private readonly AccountsDbContext _db;

    public LoginModel(AccountsDbContext db)
    {
        _db = db;
    }

    [BindProperty]
    public LoginInput Input { get; set; } = new();

    public string? Error { get; set; }
    public string? Success { get; set; }

    public void OnGet(bool created = false)
    {
        if (created)
        {
            Success = "Account created successfully. Please log in.";
        }
    }

    public async Task<IActionResult> OnPostAsync()
    {
        if (!ModelState.IsValid)
            return Page();

        var user = await _db.UserCredentials.FirstOrDefaultAsync(u => u.Email == Input.Email);
        if (user == null || user.Password != HashPassword(Input.Password))
        {
            Error = "Invalid credentials";
            return Page();
        }

        // TODO: Add authentication cookie or JWT handling
        Success = "Login successful";
        // TODO: set auth cookie then redirect
        return RedirectToPage("/Index");
    }

    /* ---- Helpers ---- */
    private static string HashPassword(string plain)
    {
        using var sha = SHA256.Create();
        var bytes = sha.ComputeHash(Encoding.UTF8.GetBytes(plain));
        return Convert.ToHexString(bytes);
    }

    public class LoginInput
    {
        [Required, EmailAddress]
        public string Email { get; set; } = string.Empty;

        [Required]
        [DataType(DataType.Password)]
        public string Password { get; set; } = string.Empty;
    }
} 
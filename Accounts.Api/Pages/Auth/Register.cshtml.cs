using Accounts.Api.DTOs;
using Accounts.Data.Models;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.RazorPages;
using Microsoft.EntityFrameworkCore;
using System.ComponentModel.DataAnnotations;
using System.Security.Cryptography;
using System.Text;

namespace Accounts.Api.Pages.Auth;

public class RegisterModel : PageModel
{
    private readonly AccountsDbContext _db;

    public RegisterModel(AccountsDbContext db)
    {
        _db = db;
    }

    [BindProperty]
    public RegisterInput Input { get; set; } = new();

    public string? Error { get; set; }
    public string? Success { get; set; }

    public void OnGet() { }

    public async Task<IActionResult> OnPostAsync()
    {
        if (!ModelState.IsValid)
            return Page();

        var exists = await _db.UserCredentials.AnyAsync(u => u.Email == Input.Email);
        if (exists)
        {
            Error = "Email already exists";
            return Page();
        }

        var user = new UserCredentials
        {
            Email = Input.Email,
            Fname = Input.FirstName,
            Lname = Input.LastName,
            Password = HashPassword(Input.Password),
            UserLevel = 0,
            IsStudent = true,
            Creationtime = DateTime.UtcNow
        };

        _db.UserCredentials.Add(user);
        await _db.SaveChangesAsync();

        Success = "Account created successfully. You can now log in.";
        return RedirectToPage("Login", new { created = true });
    }

    /* ---- Helpers ---- */
    private static string HashPassword(string plain)
    {
        using var sha = SHA256.Create();
        var bytes = sha.ComputeHash(Encoding.UTF8.GetBytes(plain));
        return Convert.ToHexString(bytes);
    }

    public class RegisterInput
    {
        [Required, EmailAddress]
        public string Email { get; set; } = string.Empty;

        [Required]
        [Display(Name = "First Name")]
        public string FirstName { get; set; } = string.Empty;

        [Display(Name = "Middle Name")]
        public string? MiddleName { get; set; }

        [Required]
        [Display(Name = "Last Name")]
        public string LastName { get; set; } = string.Empty;

        [Required]
        [DataType(DataType.Password)]
        [MinLength(6)]
        public string Password { get; set; } = string.Empty;

        [Required]
        [DataType(DataType.Password)]
        [Display(Name = "Confirm Password")]
        [Compare(nameof(Password))]
        public string ConfirmPassword { get; set; } = string.Empty;
    }
} 
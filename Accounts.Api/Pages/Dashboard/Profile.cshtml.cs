using Accounts.Data.Models;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.RazorPages;
using Microsoft.EntityFrameworkCore;
using System.ComponentModel.DataAnnotations;
using Microsoft.AspNetCore.Hosting;
using Microsoft.AspNetCore.Http;
using System.IO;

namespace Accounts.Api.Pages.Dashboard;

public class ProfileModel : PageModel
{
    private readonly AccountsDbContext _db;
    private readonly IWebHostEnvironment _env;

    public ProfileModel(AccountsDbContext db, IWebHostEnvironment env)
    {
        _db = db;
        _env = env;
    }

    [BindProperty]
    public ProfileInput Input { get; set; } = new();

    public string? Error { get; set; }
    public string? Success { get; set; }

    // Add properties for password change
    [BindProperty]
    public ChangePasswordInput PasswordInput { get; set; } = new();

    public bool IsStudent { get; set; }
    public string? ProfilePicturePath { get; set; }

    // File upload
    [BindProperty]
    public IFormFile? NewPicture { get; set; }

    public async Task<IActionResult> OnGetAsync(string? uid)
    {
        var user = await GetUserAsync(uid);
        if (user == null) return RedirectToPage("/Auth/Login");

        Input.FirstName  = user.Fname ?? string.Empty;
        Input.MiddleName = user.Mname ?? string.Empty;
        Input.LastName   = user.Lname ?? string.Empty;
        IsStudent = user.IsStudent;
        ProfilePicturePath = user.Profilepicture;
        return Page();
    }

    public async Task<IActionResult> OnPostAsync(string? uid)
    {
        if (!ModelState.IsValid) return Page();
        var user = await GetUserAsync(uid);
        if (user == null) { Error = "User not found"; return Page(); }
        if (user.IsStudent)
        {
            Error = "Students cannot modify their name information as it is managed by the university system.";
            return Page();
        }

        user.Fname = Input.FirstName;
        user.Mname = string.IsNullOrWhiteSpace(Input.MiddleName) ? null : Input.MiddleName;
        user.Lname = Input.LastName;
        await _db.SaveChangesAsync();
        Success = "Profile updated";
        return Page();
    }

    public async Task<IActionResult> OnPostChangePasswordAsync(string? uid)
    {
        if (!ModelState.IsValid) return Page();
        var user = await GetUserAsync(uid);
        if (user == null) { Error = "User not found"; return Page(); }

        // Validate current password
        if (!BCrypt.Net.BCrypt.Verify(PasswordInput.CurrentPassword, user.Password))
        {
            ModelState.AddModelError("PasswordInput.CurrentPassword", "Current password is incorrect");
            return Page();
        }

        if (PasswordInput.NewPassword != PasswordInput.ConfirmPassword)
        {
            ModelState.AddModelError("PasswordInput.ConfirmPassword", "Passwords do not match");
            return Page();
        }

        if (PasswordInput.NewPassword.Length < 8)
        {
            ModelState.AddModelError("PasswordInput.NewPassword", "Password must be at least 8 characters long");
            return Page();
        }

        user.Password = BCrypt.Net.BCrypt.HashPassword(PasswordInput.NewPassword);
        await _db.SaveChangesAsync();
        Success = "Password updated successfully";
        // Clear input fields
        PasswordInput = new();
        return Page();
    }

    public async Task<IActionResult> OnPostUploadPictureAsync(string? uid)
    {
        var user = await GetUserAsync(uid);
        if (user == null) { Error = "User not found"; return Page(); }

        if (NewPicture == null || NewPicture.Length == 0)
        {
            ModelState.AddModelError("NewPicture", "Please select an image file");
            return Page();
        }

        var allowed = new[] { ".jpg", ".jpeg", ".png", ".gif" };
        var ext = Path.GetExtension(NewPicture.FileName).ToLowerInvariant();
        if (!allowed.Contains(ext))
        {
            ModelState.AddModelError("NewPicture", "Invalid file type");
            return Page();
        }
        if (NewPicture.Length > 5 * 1024 * 1024)
        {
            ModelState.AddModelError("NewPicture", "File too large (max 5MB)");
            return Page();
        }

        // Ensure directory
        var picDir = Path.Combine(_env.WebRootPath ?? "wwwroot", "profilePictures");
        if (!Directory.Exists(picDir)) Directory.CreateDirectory(picDir);

        var fileName = $"{user.Uid}_{DateTimeOffset.UtcNow.ToUnixTimeSeconds()}{ext}";
        var fullPath = Path.Combine(picDir, fileName);
        using (var fs = System.IO.File.Create(fullPath))
        {
            await NewPicture.CopyToAsync(fs);
        }

        user.Profilepicture = fileName;
        await _db.SaveChangesAsync();
        Success = "Profile picture updated";
        ProfilePicturePath = fileName;
        return Page();
    }

    private async Task<UserCredentials?> GetUserAsync(string? uid)
    {
        if (!string.IsNullOrWhiteSpace(uid))
            return await _db.UserCredentials.FirstOrDefaultAsync(u => u.Uid == uid);
        return await _db.UserCredentials.FirstOrDefaultAsync(); // fallback first user
    }

    public class ProfileInput
    {
        [Required, Display(Name="First Name")]
        public string FirstName { get; set; } = string.Empty;
        [Display(Name="Middle Name")]
        public string? MiddleName { get; set; }
        [Required, Display(Name="Last Name")]
        public string LastName { get; set; } = string.Empty;
    }

    public class ChangePasswordInput
    {
        [Required]
        [DataType(DataType.Password)]
        [Display(Name = "Current Password")]
        public string CurrentPassword { get; set; } = string.Empty;

        [Required]
        [DataType(DataType.Password)]
        [Display(Name = "New Password")]
        public string NewPassword { get; set; } = string.Empty;

        [Required]
        [DataType(DataType.Password)]
        [Compare(nameof(NewPassword), ErrorMessage = "Passwords do not match")]
        [Display(Name = "Confirm Password")]
        public string ConfirmPassword { get; set; } = string.Empty;
    }
} 
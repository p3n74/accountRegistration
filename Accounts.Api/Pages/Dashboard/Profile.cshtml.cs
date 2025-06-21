using Accounts.Data.Models;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.RazorPages;
using Microsoft.EntityFrameworkCore;
using System.ComponentModel.DataAnnotations;

namespace Accounts.Api.Pages.Dashboard;

public class ProfileModel : PageModel
{
    private readonly AccountsDbContext _db;

    public ProfileModel(AccountsDbContext db)
    {
        _db = db;
    }

    [BindProperty]
    public ProfileInput Input { get; set; } = new();

    public string? Error { get; set; }
    public string? Success { get; set; }

    public async Task<IActionResult> OnGetAsync(string? uid)
    {
        var user = await GetUserAsync(uid);
        if (user == null) return RedirectToPage("/Auth/Login");

        Input.FirstName  = user.Fname ?? string.Empty;
        Input.MiddleName = user.Mname ?? string.Empty;
        Input.LastName   = user.Lname ?? string.Empty;
        return Page();
    }

    public async Task<IActionResult> OnPostAsync(string? uid)
    {
        if (!ModelState.IsValid) return Page();
        var user = await GetUserAsync(uid);
        if (user == null) { Error = "User not found"; return Page(); }

        user.Fname = Input.FirstName;
        user.Mname = string.IsNullOrWhiteSpace(Input.MiddleName) ? null : Input.MiddleName;
        user.Lname = Input.LastName;
        await _db.SaveChangesAsync();
        Success = "Profile updated";
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
} 
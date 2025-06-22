using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.RazorPages;

namespace Accounts.Api.Pages.Auth;

public class LogoutModel : PageModel
{
    public IActionResult OnGet()
    {
        // Clear the session
        HttpContext.Session.Clear();
        
        // Set success message for login page
        TempData["SuccessMessage"] = "You have been successfully signed out.";
        
        // Show logout page briefly before redirect
        return Page();
    }

    public IActionResult OnPost()
    {
        // Handle POST logout (from forms)
        HttpContext.Session.Clear();
        TempData["SuccessMessage"] = "You have been successfully signed out.";
        return RedirectToPage("Login");
    }
} 
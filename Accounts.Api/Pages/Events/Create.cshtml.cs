using Accounts.Data.Models;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.RazorPages;
using Microsoft.EntityFrameworkCore;
using System.ComponentModel.DataAnnotations;

namespace Accounts.Api.Pages.Events;

public class CreateModel : PageModel
{
    private readonly AccountsDbContext _db;
    private readonly IWebHostEnvironment _env;
    
    public CreateModel(AccountsDbContext db, IWebHostEnvironment env) 
    { 
        _db = db; 
        _env = env;
    }

    [BindProperty] public EventInput Input { get; set; } = new();
    public string? Error { get; set; }
    public string? Success { get; set; }

    public void OnGet() 
    {
        // Set default times (start: 1 hour from now, end: 2 hours from now)
        var now = DateTime.Now;
        Input.StartDate = now.AddHours(1);
        Input.EndDate = now.AddHours(3);
    }

    public async Task<IActionResult> OnPostAsync(string? uid)
    {
        if (!ModelState.IsValid) return Page();

        // Validate dates
        if (Input.EndDate <= Input.StartDate)
        {
            Error = "End date must be after start date";
            return Page();
        }

        if (Input.StartDate < DateTime.Now)
        {
            Error = "Start date cannot be in the past";
            return Page();
        }

        var creator = await _db.UserCredentials.FirstOrDefaultAsync(u => u.Uid == uid) 
                     ?? await _db.UserCredentials.FirstOrDefaultAsync();
        if (creator == null) 
        { 
            Error = "User not found"; 
            return Page(); 
        }

        var eventId = Guid.NewGuid().ToString("N");
        string? badgePath = null;

        // Handle file upload
        if (Input.EventBadge != null && Input.EventBadge.Length > 0)
        {
            // Validate file
            if (Input.EventBadge.Length > 5 * 1024 * 1024) // 5MB limit
            {
                Error = "Badge file must be smaller than 5MB";
                return Page();
            }

            var allowedTypes = new[] { "image/jpeg", "image/jpg", "image/png", "image/gif" };
            if (!allowedTypes.Contains(Input.EventBadge.ContentType.ToLower()))
            {
                Error = "Badge must be a valid image file (JPG, PNG, GIF)";
                return Page();
            }

            try
            {
                // Create uploads directory if it doesn't exist
                var uploadsDir = Path.Combine(_env.WebRootPath, "uploads", "events", eventId);
                Directory.CreateDirectory(uploadsDir);

                // Generate unique filename
                var extension = Path.GetExtension(Input.EventBadge.FileName);
                var fileName = $"badge_{Guid.NewGuid()}{extension}";
                var filePath = Path.Combine(uploadsDir, fileName);

                // Save file
                using (var stream = new FileStream(filePath, FileMode.Create))
                {
                    await Input.EventBadge.CopyToAsync(stream);
                }

                badgePath = $"/uploads/events/{eventId}/{fileName}";
            }
            catch (Exception ex)
            {
                Error = "Failed to upload badge image: " + ex.Message;
                return Page();
            }
        }

        // Generate event key
        var eventKey = GenerateEventKey();

        var ev = new Accounts.Data.Models.Events
        {
            Eventid = eventId,
            Eventname = Input.EventName,
            Startdate = Input.StartDate,
            Enddate = Input.EndDate,
            Location = Input.Location,
            Eventshortinfo = Input.ShortInfo,
            Eventinfopath = Input.LongInfo, // Store full info directly for now
            Eventbadgepath = badgePath,
            Eventkey = eventKey,
            Eventcreator = creator.Uid,
            EventcreatorNavigation = creator,
            Participantcount = 0,
            Views = 0
        };
        
        _db.Events.Add(ev);
        await _db.SaveChangesAsync();
        
        Success = $"Event created successfully! Share this registration link: {Request.Scheme}://{Request.Host}/events/register/{eventKey}";
        return RedirectToPage("/Dashboard/Index");
    }

    private static string GenerateEventKey()
    {
        return Convert.ToBase64String(Guid.NewGuid().ToByteArray())
            .Replace("=", "")
            .Replace("+", "")
            .Replace("/", "")
            .Substring(0, 8)
            .ToUpper();
    }

    public class EventInput
    {
        [Required]
        [Display(Name="Event Name")]
        [StringLength(255, ErrorMessage = "Event name cannot be longer than 255 characters")]
        public string EventName { get; set; } = string.Empty;

        [Required]
        [Display(Name="Start Date")]
        public DateTime StartDate { get; set; }

        [Required]
        [Display(Name="End Date")]
        public DateTime EndDate { get; set; }

        [Required]
        [StringLength(500, ErrorMessage = "Location cannot be longer than 500 characters")]
        public string Location { get; set; } = string.Empty;
        
        [Display(Name="Short Description")]
        [StringLength(1000, ErrorMessage = "Short description cannot be longer than 1000 characters")]
        public string? ShortInfo { get; set; }
        
        [Display(Name="Detailed Information")]
        public string? LongInfo { get; set; }

        [Display(Name="Event Badge")]
        public IFormFile? EventBadge { get; set; }
    }
} 
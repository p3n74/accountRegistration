using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.RazorPages;
using Microsoft.EntityFrameworkCore;
using Accounts.Data.Models;
using System.Text.Json;

namespace Accounts.Api.Pages.Dashboard
{
    public class IndexModel : PageModel
    {
        private readonly AccountsDbContext _context;
        private readonly ILogger<IndexModel> _logger;

        public IndexModel(AccountsDbContext context, ILogger<IndexModel> logger)
        {
            _context = context;
            _logger = logger;
        }

        // User Information
        public string UserName { get; set; } = string.Empty;
        public string Email { get; set; } = string.Empty;
        public bool IsStudent { get; set; }
        public string? ProgramName { get; set; }
        public string? ProfilePicture { get; set; }
        public DateTime LastLogin { get; set; }
        public string LastLoginFormatted => LastLogin.ToString("MMM dd, yyyy 'at' h:mm tt");

        // Statistics
        public int AttendedEventsCount { get; set; }
        public int BadgesCount { get; set; }
        public int UpcomingEventsCount { get; set; }
        public int DaysAsMember { get; set; }
        public int ProfileCompletionPercentage { get; set; }

        // Data Collections
        public List<EventSummary> UpcomingEvents { get; set; } = new();
        public List<ActivityItem> RecentActivities { get; set; } = new();
        public List<NotificationItem> Notifications { get; set; } = new();

        public async Task<IActionResult> OnGetAsync()
        {
            // Check if user is logged in
            var userId = HttpContext.Session.GetString("uid");
            if (string.IsNullOrEmpty(userId))
            {
                return RedirectToPage("/Auth/Login");
            }

            try
            {
                            // Load user information
            var user = await _context.UserCredentials
                .FirstOrDefaultAsync(u => u.Uid == userId);

            if (user == null)
            {
                HttpContext.Session.Clear();
                return RedirectToPage("/Auth/Login");
            }

            // Set user information
            UserName = $"{user.Fname} {user.Lname}".Trim();
            Email = user.Email;
            IsStudent = user.IsStudent;
            ProgramName = null; // Will load separately if needed
            ProfilePicture = user.Profilepicture;
            LastLogin = user.Creationtime ?? DateTime.UtcNow;

                            // Calculate days as member
            DaysAsMember = (DateTime.UtcNow - (user.Creationtime ?? DateTime.UtcNow)).Days;

                // Load statistics
                await LoadStatistics(userId);

                // Load upcoming events
                await LoadUpcomingEvents();

                // Load recent activities
                await LoadRecentActivities(userId);

                // Load notifications
                await LoadNotifications(userId);

                // Calculate profile completion
                CalculateProfileCompletion(user);

                return Page();
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error loading dashboard for user: {UserId}", userId);
                TempData["ErrorMessage"] = "An error occurred while loading your dashboard. Please try again.";
                return Page();
            }
        }

        private async Task LoadStatistics(string userId)
        {
            // Count attended events (simplified for now)
            AttendedEventsCount = 0; // Will implement later when event system is complete

            // Count badges (assuming each attended event gives a badge)
            BadgesCount = AttendedEventsCount;

            // Count upcoming events
            UpcomingEventsCount = await _context.Events
                .Where(e => e.Startdate > DateTime.UtcNow)
                .CountAsync();
        }

        private async Task LoadUpcomingEvents()
        {
            var events = await _context.Events
                .Where(e => e.Startdate > DateTime.UtcNow)
                .OrderBy(e => e.Startdate)
                .Take(10)
                .Select(e => new EventSummary
                {
                    EventId = int.Parse(e.Eventid),
                    EventTitle = e.Eventname ?? "Untitled Event",
                    EventDescription = e.Eventshortinfo ?? "",
                    EventDate = e.Startdate ?? DateTime.UtcNow,
                    EventLocation = e.Location ?? ""
                })
                .ToListAsync();

            UpcomingEvents = events;
        }

        private async Task LoadRecentActivities(string userId)
        {
            var activities = new List<ActivityItem>();

            try
            {
                // Add some sample activities for now
                activities.Add(new ActivityItem
                {
                    Description = "Joined CISCO community",
                    Icon = "fas fa-user-plus text-primary",
                    TimeAgo = GetTimeAgo(DateTime.UtcNow.AddDays(-1))
                });

                RecentActivities = activities;
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error loading recent activities for user: {UserId}", userId);
                RecentActivities = new List<ActivityItem>();
            }
        }

        private async Task LoadNotifications(string userId)
        {
            try
            {
                // Simplified for now - no notifications table setup yet
                Notifications = new List<NotificationItem>();
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error loading notifications for user: {UserId}", userId);
                Notifications = new List<NotificationItem>();
            }
        }

        private void CalculateProfileCompletion(UserCredentials user)
        {
            int completedFields = 0;
            int totalFields = 6;

            // Required fields
            if (!string.IsNullOrEmpty(user.Fname)) completedFields++;
            if (!string.IsNullOrEmpty(user.Lname)) completedFields++;
            if (!string.IsNullOrEmpty(user.Email)) completedFields++;

            // Optional but recommended fields
            if (!string.IsNullOrEmpty(user.Mname)) completedFields++;
            if (!string.IsNullOrEmpty(user.Profilepicture)) completedFields++;
            if (user.Emailverified == true) completedFields++;

            ProfileCompletionPercentage = (int)Math.Round((double)completedFields / totalFields * 100);
        }

        private static string GetTimeAgo(DateTime dateTime)
        {
            var timeSpan = DateTime.UtcNow - dateTime;

            if (timeSpan.TotalMinutes < 1)
                return "Just now";
            if (timeSpan.TotalMinutes < 60)
                return $"{(int)timeSpan.TotalMinutes} minutes ago";
            if (timeSpan.TotalHours < 24)
                return $"{(int)timeSpan.TotalHours} hours ago";
            if (timeSpan.TotalDays < 7)
                return $"{(int)timeSpan.TotalDays} days ago";
            if (timeSpan.TotalDays < 30)
                return $"{(int)(timeSpan.TotalDays / 7)} weeks ago";
            if (timeSpan.TotalDays < 365)
                return $"{(int)(timeSpan.TotalDays / 30)} months ago";

            return $"{(int)(timeSpan.TotalDays / 365)} years ago";
        }

        public class EventSummary
        {
            public int EventId { get; set; }
            public string EventTitle { get; set; } = string.Empty;
            public string? EventDescription { get; set; }
            public DateTime EventDate { get; set; }
            public string? EventLocation { get; set; }
        }

        public class ActivityItem
        {
            public string Description { get; set; } = string.Empty;
            public string Icon { get; set; } = string.Empty;
            public string TimeAgo { get; set; } = string.Empty;
        }

        public class NotificationItem
        {
            public string Message { get; set; } = string.Empty;
            public bool IsRead { get; set; }
            public string TimeAgo { get; set; } = string.Empty;
        }
    }
} 
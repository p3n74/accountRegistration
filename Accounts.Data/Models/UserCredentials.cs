using System;
using System.Collections.Generic;

namespace Accounts.Data.Models;

public partial class UserCredentials
{
    public string Uid { get; set; } = null!;

    public string? Fname { get; set; }

    public string? Mname { get; set; }

    public string? Lname { get; set; }

    public string Email { get; set; } = null!;

    public string Password { get; set; } = null!;

    public string? Currboundtoken { get; set; }

    public bool? Emailverified { get; set; }

    public string? Attendedevents { get; set; }

    public DateTime? Creationtime { get; set; }

    public string? Profilepicture { get; set; }

    public string? PasswordResetToken { get; set; }

    public DateTime? PasswordResetExpiry { get; set; }

    public string? VerificationCode { get; set; }

    public string? NewEmail { get; set; }

    public string? Fullname { get; set; }

    public bool IsStudent { get; set; }

    public sbyte UserLevel { get; set; }

    public int ProgramId { get; set; }

    public virtual ICollection<Events> Events { get; set; } = new List<Events>();
}

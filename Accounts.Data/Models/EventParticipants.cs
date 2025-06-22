using System;
using System.Collections.Generic;

namespace Accounts.Data.Models;

public partial class EventParticipants
{
    public Guid ParticipantId { get; set; }

    public Guid EventId { get; set; }

    public Guid? Uid { get; set; }

    public string Email { get; set; } = null!;

    public DateTime JoinedAt { get; set; }

    public bool? Registered { get; set; }

    public int AttendanceStatus { get; set; } = 0; // 0=Invited, 1=Pending, 2=Paid, 3=Attended, 4=Absent, 5=Awaiting Verification
}

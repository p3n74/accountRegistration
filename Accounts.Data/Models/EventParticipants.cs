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

    public bool AttendanceStatus { get; set; }
}

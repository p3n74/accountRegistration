using System;
using System.Collections.Generic;

namespace Accounts.Data.Models;

public partial class Notifications
{
    public ulong NotifId { get; set; }

    public Guid RecipientUid { get; set; }

    public Guid? ChannelId { get; set; }

    public string Title { get; set; } = null!;

    public string? Body { get; set; }

    public bool Seen { get; set; }

    public DateTime CreatedAt { get; set; }

    public virtual Channels? Channel { get; set; }
}

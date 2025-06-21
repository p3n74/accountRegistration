using System;
using System.Collections.Generic;

namespace Accounts.Data.Models;

public partial class Channels
{
    public Guid ChannelId { get; set; }

    public string ChannelType { get; set; } = null!;

    public string? RelatedId { get; set; }

    public string? Name { get; set; }

    public Guid? CreatedBy { get; set; }

    public DateTime CreatedAt { get; set; }

    public virtual ICollection<ChannelMembers> ChannelMembers { get; set; } = new List<ChannelMembers>();

    public virtual ICollection<Messages> Messages { get; set; } = new List<Messages>();

    public virtual ICollection<Notifications> Notifications { get; set; } = new List<Notifications>();
}

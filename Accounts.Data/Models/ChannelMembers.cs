using System;
using System.Collections.Generic;

namespace Accounts.Data.Models;

public partial class ChannelMembers
{
    public Guid ChannelId { get; set; }

    public Guid Uid { get; set; }

    public string? Role { get; set; }

    public DateTime JoinedAt { get; set; }

    public virtual Channels Channel { get; set; } = null!;
}

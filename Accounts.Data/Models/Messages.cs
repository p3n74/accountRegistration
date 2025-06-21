using System;
using System.Collections.Generic;

namespace Accounts.Data.Models;

public partial class Messages
{
    public ulong MsgId { get; set; }

    public Guid ChannelId { get; set; }

    public Guid? SenderUid { get; set; }

    public string Body { get; set; } = null!;

    public DateTime CreatedAt { get; set; }

    public virtual Channels Channel { get; set; } = null!;
}

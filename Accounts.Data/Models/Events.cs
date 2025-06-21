using System;
using System.Collections.Generic;

namespace Accounts.Data.Models;

public partial class Events
{
    public string Eventid { get; set; } = null!;

    public int Participantcount { get; set; }

    public DateTime? Startdate { get; set; }

    public DateTime? Enddate { get; set; }

    public string? Eventinfopath { get; set; }

    public string? Location { get; set; }

    public string? Eventname { get; set; }

    public string? Eventbadgepath { get; set; }

    public string? Eventcreator { get; set; }

    public string? Eventkey { get; set; }

    public string? Eventshortinfo { get; set; }

    public uint Views { get; set; }

    public virtual UserCredentials? EventcreatorNavigation { get; set; }
}

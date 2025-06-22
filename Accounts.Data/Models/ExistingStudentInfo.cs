using Microsoft.EntityFrameworkCore;
using System.ComponentModel.DataAnnotations.Schema;

namespace Accounts.Data.Models;

[Keyless]
public partial class ExistingStudentInfo
{
    [Column("email")]
    public string Email { get; set; } = null!;

    [Column("first_name")]
    public string FirstName { get; set; } = null!;

    [Column("middle_name")]
    public string? MiddleName { get; set; }

    [Column("last_name")]
    public string LastName { get; set; } = null!;

    [Column("program_id")]
    public int? ProgramId { get; set; }
} 
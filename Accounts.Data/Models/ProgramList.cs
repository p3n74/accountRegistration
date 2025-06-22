using System.ComponentModel.DataAnnotations.Schema;

namespace Accounts.Data.Models;

[Table("program_list")]
public partial class ProgramList
{
    [Column("program_id")]
    public int ProgramId { get; set; }

    [Column("program_name")]
    public string ProgramName { get; set; } = null!;

    [Column("department_id")]
    public int DepartmentId { get; set; }

    [Column("level_id")]
    public int LevelId { get; set; }
} 
using System;
using System.Collections.Generic;
using Microsoft.EntityFrameworkCore;
using Pomelo.EntityFrameworkCore.MySql.Scaffolding.Internal;

namespace Accounts.Data.Models;

public partial class AccountsDbContext : DbContext
{
    public AccountsDbContext()
    {
    }

    public AccountsDbContext(DbContextOptions<AccountsDbContext> options)
        : base(options)
    {
    }

    public virtual DbSet<UserCredentials> UserCredentials { get; set; }

    public virtual DbSet<EventParticipants> EventParticipants { get; set; }

    public virtual DbSet<Events> Events { get; set; }

    public virtual DbSet<ChannelMembers> ChannelMembers { get; set; }

    public virtual DbSet<Channels> Channels { get; set; }

    public virtual DbSet<Messages> Messages { get; set; }

    public virtual DbSet<Notifications> Notifications { get; set; }

    public virtual DbSet<ExistingStudentInfo> ExistingStudentInfos { get; set; }

    public virtual DbSet<ProgramList> ProgramLists { get; set; }

    protected override void OnConfiguring(DbContextOptionsBuilder optionsBuilder)
#warning To protect potentially sensitive information in your connection string, you should move it out of source code. You can avoid scaffolding the connection string by using the Name= syntax to read it from configuration - see https://go.microsoft.com/fwlink/?linkid=2131148. For more guidance on storing connection strings, see https://go.microsoft.com/fwlink/?LinkId=723263.
        => optionsBuilder.UseMySql("server=127.0.0.1;database=s21102134_palisade;user=s21102134_palisade;password=webwebwebweb", Microsoft.EntityFrameworkCore.ServerVersion.Parse("10.11.11-mariadb"));

    protected override void OnModelCreating(ModelBuilder modelBuilder)
    {
        modelBuilder
            .UseCollation("utf8mb4_general_ci")
            .HasCharSet("utf8mb4");

        modelBuilder.Entity<EventParticipants>(entity =>
        {
            entity.HasKey(e => e.ParticipantId).HasName("PRIMARY");

            entity.ToTable("event_participants");

            entity.HasIndex(e => e.EventId, "idx_event");

            entity.HasIndex(e => e.Uid, "idx_uid");

            entity.HasIndex(e => new { e.EventId, e.Email }, "uniq_event_email").IsUnique();

            entity.Property(e => e.ParticipantId).HasColumnName("participant_id");
            entity.Property(e => e.AttendanceStatus)
                .HasColumnName("attendance_status")
                .HasDefaultValue(0);
            entity.Property(e => e.Email).HasColumnName("email");
            entity.Property(e => e.EventId).HasColumnName("event_id");
            entity.Property(e => e.JoinedAt)
                .HasDefaultValueSql("current_timestamp()")
                .HasColumnType("datetime")
                .HasColumnName("joined_at");
            entity.Property(e => e.Registered)
                .IsRequired()
                .HasDefaultValueSql("'1'")
                .HasColumnName("registered");
            entity.Property(e => e.Uid).HasColumnName("uid");
        });

        modelBuilder.Entity<ChannelMembers>(entity =>
        {
            entity.HasKey(e => new { e.ChannelId, e.Uid }).HasName("PRIMARY");

            entity.ToTable("channel_members");

            entity.Property(e => e.ChannelId).HasColumnName("channel_id");
            entity.Property(e => e.Uid).HasColumnName("uid");
            entity.Property(e => e.Role).HasColumnName("role");
            entity.Property(e => e.JoinedAt)
                .HasDefaultValueSql("current_timestamp()")
                .HasColumnType("datetime")
                .HasColumnName("joined_at");

            entity.HasOne(d => d.Channel)
                .WithMany(p => p.ChannelMembers)
                .HasForeignKey(d => d.ChannelId);
        });

        modelBuilder.Entity<Channels>(entity =>
        {
            entity.HasKey(e => e.ChannelId).HasName("PRIMARY");
            entity.ToTable("channels");
            entity.Property(e => e.ChannelId).HasColumnName("channel_id");
            entity.Property(e => e.ChannelType).HasColumnName("channel_type");
            entity.Property(e => e.RelatedId).HasColumnName("related_id");
            entity.Property(e => e.Name).HasColumnName("name");
            entity.Property(e => e.CreatedBy).HasColumnName("created_by");
            entity.Property(e => e.CreatedAt)
                .HasDefaultValueSql("current_timestamp()")
                .HasColumnType("datetime")
                .HasColumnName("created_at");
        });

        modelBuilder.Entity<Messages>(entity =>
        {
            entity.HasKey(e => e.MsgId).HasName("PRIMARY");
            entity.ToTable("messages");
            entity.Property(e => e.MsgId).HasColumnName("msg_id");
            entity.Property(e => e.ChannelId).HasColumnName("channel_id");
            entity.Property(e => e.SenderUid).HasColumnName("sender_uid");
            entity.Property(e => e.Body).HasColumnName("body");
            entity.Property(e => e.CreatedAt)
                .HasDefaultValueSql("current_timestamp()")
                .HasColumnType("datetime")
                .HasColumnName("created_at");

            entity.HasOne(d => d.Channel)
                .WithMany(p => p.Messages)
                .HasForeignKey(d => d.ChannelId);
        });

        modelBuilder.Entity<Notifications>(entity =>
        {
            entity.HasKey(e => e.NotifId).HasName("PRIMARY");
            entity.ToTable("notifications");
            entity.Property(e => e.NotifId).HasColumnName("notif_id");
            entity.Property(e => e.RecipientUid).HasColumnName("recipient_uid");
            entity.Property(e => e.ChannelId).HasColumnName("channel_id");
            entity.Property(e => e.Title).HasColumnName("title");
            entity.Property(e => e.Body).HasColumnName("body");
            entity.Property(e => e.Seen).HasColumnName("seen");
            entity.Property(e => e.CreatedAt)
                .HasDefaultValueSql("current_timestamp()")
                .HasColumnType("datetime")
                .HasColumnName("created_at");

            entity.HasOne(d => d.Channel)
                .WithMany(p => p.Notifications)
                .HasForeignKey(d => d.ChannelId);
        });

        modelBuilder.Entity<Events>(entity =>
        {
            entity.HasKey(e => e.Eventid).HasName("PRIMARY");
            entity.ToTable("events");
            entity.Property(e => e.Eventid).HasColumnName("eventid");
            entity.Property(e => e.Participantcount).HasColumnName("participantcount");
            entity.Property(e => e.Startdate).HasColumnName("startdate");
            entity.Property(e => e.Enddate).HasColumnName("enddate");
            entity.Property(e => e.Eventinfopath).HasColumnName("eventinfopath");
            entity.Property(e => e.Location).HasColumnName("location");
            entity.Property(e => e.Eventname).HasColumnName("eventname");
            entity.Property(e => e.Eventbadgepath).HasColumnName("eventbadgepath");
            entity.Property(e => e.Eventcreator).HasColumnName("eventcreator");
            entity.Property(e => e.Eventkey).HasColumnName("eventkey");
            entity.Property(e => e.Eventshortinfo).HasColumnName("eventshortinfo");
            entity.Property(e => e.Views).HasColumnName("views");

            entity.HasOne(d => d.EventcreatorNavigation)
                .WithMany(p => p.Events)
                .HasForeignKey(d => d.Eventcreator);
        });

        modelBuilder.Entity<UserCredentials>(entity =>
        {
            entity.HasKey(e => e.Uid).HasName("PRIMARY");
            entity.ToTable("user_credentials");
            entity.Property(e => e.Uid).HasColumnName("uid");
            entity.Property(e => e.Fname).HasColumnName("fname");
            entity.Property(e => e.Mname).HasColumnName("mname");
            entity.Property(e => e.Lname).HasColumnName("lname");
            entity.Property(e => e.Email).HasColumnName("email");
            entity.Property(e => e.Password).HasColumnName("password");
            entity.Property(e => e.Currboundtoken).HasColumnName("currboundtoken");
            entity.Property(e => e.Emailverified).HasColumnName("emailverified");
            entity.Property(e => e.Attendedevents).HasColumnName("attendedevents");
            entity.Property(e => e.Creationtime).HasColumnName("creationtime");
            entity.Property(e => e.Profilepicture).HasColumnName("profilepicture");
            entity.Property(e => e.PasswordResetToken).HasColumnName("password_reset_token");
            entity.Property(e => e.PasswordResetExpiry).HasColumnName("password_reset_expiry");
            entity.Property(e => e.VerificationCode).HasColumnName("verification_code");
            entity.Property(e => e.NewEmail).HasColumnName("new_email");
            entity.Property(e => e.Fullname).HasColumnName("fullname");
            entity.Property(e => e.IsStudent).HasColumnName("is_student");
            entity.Property(e => e.UserLevel).HasColumnName("user_level");
            entity.Property(e => e.ProgramId).HasColumnName("program_id");
        });

        modelBuilder.Entity<ExistingStudentInfo>(entity =>
        {
            entity.HasNoKey();
            entity.ToTable("existing_student_info");
            entity.Property(e => e.Email).HasColumnName("email");
            entity.Property(e => e.FirstName).HasColumnName("first_name");
            entity.Property(e => e.MiddleName).HasColumnName("middle_name");
            entity.Property(e => e.LastName).HasColumnName("last_name");
            entity.Property(e => e.ProgramId).HasColumnName("program_id");
        });

        modelBuilder.Entity<ProgramList>(entity =>
        {
            entity.HasKey(e => e.ProgramId).HasName("PRIMARY");
            entity.ToTable("program_list");
            entity.Property(e => e.ProgramId).HasColumnName("program_id");
            entity.Property(e => e.ProgramName).HasColumnName("program_name");
            entity.Property(e => e.DepartmentId).HasColumnName("department_id");
            entity.Property(e => e.LevelId).HasColumnName("level_id");
        });

        OnModelCreatingPartial(modelBuilder);
    }

    partial void OnModelCreatingPartial(ModelBuilder modelBuilder);
}

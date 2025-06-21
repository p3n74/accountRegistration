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
            entity.Property(e => e.AttendanceStatus).HasColumnName("attendance_status");
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

        OnModelCreatingPartial(modelBuilder);
    }

    partial void OnModelCreatingPartial(ModelBuilder modelBuilder);
}

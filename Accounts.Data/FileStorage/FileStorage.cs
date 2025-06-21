using System.Globalization;
using System.Text.Json;
using CsvHelper;
using CsvHelper.Configuration;

namespace Accounts.Data.FileStorage;

public class FileStorage
{
    private readonly string _storageRoot;
    private readonly JsonSerializerOptions _jsonOptions = new(JsonSerializerDefaults.General)
    {
        WriteIndented = true
    };

    public FileStorage(string? storageRoot = null)
    {
        _storageRoot = storageRoot ?? Path.Combine(AppContext.BaseDirectory, "storage");
        EnsureDirectoriesExist();
    }

    private void EnsureDirectoriesExist()
    {
        var dirs = new[] { "users", "events", "participants" };
        foreach (var dir in dirs)
        {
            var path = Path.Combine(_storageRoot, dir);
            if (!Directory.Exists(path))
                Directory.CreateDirectory(path);
        }
    }

    /* -------------------- User JSON -------------------- */

    public bool SaveUserData(string uid, object data)
    {
        var file = Path.Combine(_storageRoot, "users", $"{uid}.json");
        var json = JsonSerializer.Serialize(data, _jsonOptions);
        File.WriteAllText(file, json);
        return true;
    }

    public T? GetUserData<T>(string uid)
    {
        var file = Path.Combine(_storageRoot, "users", $"{uid}.json");
        return !File.Exists(file)
            ? default
            : JsonSerializer.Deserialize<T>(File.ReadAllText(file), _jsonOptions);
    }

    /* -------------------- Event JSON -------------------- */
    private string GetEventDir(string eventId)
    {
        var dir = Path.Combine(_storageRoot, "events", eventId);
        if (!Directory.Exists(dir)) Directory.CreateDirectory(dir);
        return dir;
    }

    public bool SaveEventData(string eventId, object data)
    {
        var file = Path.Combine(GetEventDir(eventId), "meta.json");
        File.WriteAllText(file, JsonSerializer.Serialize(data, _jsonOptions));
        return true;
    }

    public T? GetEventData<T>(string eventId)
    {
        var file = Path.Combine(GetEventDir(eventId), "meta.json");
        if (!File.Exists(file))
            return default;
        return JsonSerializer.Deserialize<T>(File.ReadAllText(file), _jsonOptions);
    }

    public void DeleteEventData(string eventId)
    {
        var dir = GetEventDir(eventId);
        if (Directory.Exists(dir)) Directory.Delete(dir, true);
    }

    /* -------------------- Participants CSV -------------------- */
    private string GetParticipantsCsv(string eventId) => Path.Combine(GetEventDir(eventId), "participants.csv");

    public IReadOnlyDictionary<string, Dictionary<string, string>> GetEventParticipants(string eventId)
    {
        var file = GetParticipantsCsv(eventId);
        if (!File.Exists(file)) return new Dictionary<string, Dictionary<string, string>>();
        var cfg = new CsvConfiguration(CultureInfo.InvariantCulture) { HasHeaderRecord = true };
        using var reader = new StreamReader(file);
        using var csv = new CsvReader(reader, cfg);
        var records = csv.GetRecords<dynamic>().ToList();

        var result = new Dictionary<string, Dictionary<string, string>>();
        foreach (IDictionary<string, object?> row in records)
        {
            var dict = row.ToDictionary(k => k.Key, v => v.Value?.ToString() ?? string.Empty);
            if (dict.TryGetValue("uid", out var uid))
            {
                dict.Remove("uid");
                result[uid] = dict;
            }
        }
        return result;
    }

    public bool SaveEventParticipants(string eventId, IReadOnlyDictionary<string, Dictionary<string, string>> participants)
    {
        var file = GetParticipantsCsv(eventId);
        if (participants.Count == 0)
        {
            if (File.Exists(file)) File.Delete(file);
            return true;
        }

        var cfg = new CsvConfiguration(CultureInfo.InvariantCulture) { HasHeaderRecord = true };
        using var writer = new StreamWriter(file);
        using var csv = new CsvWriter(writer, cfg);

        // header from first row keys
        var first = participants.First();
        var headers = new[] { "uid" }.Concat(first.Value.Keys).ToArray();
        foreach (var h in headers) csv.WriteField(h);
        csv.NextRecord();

        foreach (var (uid, data) in participants)
        {
            csv.WriteField(uid);
            foreach (var h in headers.Skip(1))
            {
                csv.WriteField(data.ContainsKey(h) ? data[h] : string.Empty);
            }
            csv.NextRecord();
        }
        return true;
    }

    public bool AddParticipant(string eventId, string uid, Dictionary<string, string>? extra = null)
    {
        var participants = new Dictionary<string, Dictionary<string, string>>(GetEventParticipants(eventId));
        var data = new Dictionary<string, string>
        {
            ["joined_at"] = DateTime.UtcNow.ToString("s"),
            ["status"] = "registered"
        };
        if (extra != null)
            foreach (var kv in extra) data[kv.Key] = kv.Value;

        participants[uid] = data;
        return SaveEventParticipants(eventId, participants);
    }

    public bool RemoveParticipant(string eventId, string uid)
    {
        var participants = new Dictionary<string, Dictionary<string, string>>(GetEventParticipants(eventId));
        participants.Remove(uid);
        return SaveEventParticipants(eventId, participants);
    }
} 
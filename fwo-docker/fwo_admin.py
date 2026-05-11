"""
FWO Admin Forge - Sandbox tool for offline preservation server.
Run with: python fwo_admin.py
Open: http://localhost:5000

Bypasses in-game forging restrictions by directly modifying charinv rows.
Lets the GM forge any weapon with any 5 components — no level checks,
no skill checks, no recipe validation.

WORKFLOW:
1. In-game: place weapon in action tray slot 1, components in slots 2-6
2. Log out of the game (engine caches inventory)
3. Open this webpage, pick character, click Forge
4. Log back in to see the forged result
"""

import subprocess
from flask import Flask, render_template_string, request, redirect, url_for

app = Flask(__name__)

DB_PASSWORD = "ejair0xx"
CONTAINER = "fwo-db"


def run_sql(sql, db="fwworlddevdb"):
    """Run SQL via docker exec. Returns raw stdout (TSV format with -B flag)."""
    cmd = ["docker", "exec", CONTAINER, "mysql", "-u", "root",
           f"-p{DB_PASSWORD}", "-B", "-e", sql, db]
    result = subprocess.run(cmd, capture_output=True, text=True)
    if result.returncode != 0:
        raise RuntimeError(f"SQL error: {result.stderr}")
    return result.stdout


def parse_tsv(output):
    """Parse mysql -B output (tab-separated) into list of dicts."""
    lines = output.strip().split("\n")
    if len(lines) < 2:
        return []
    headers = lines[0].split("\t")
    rows = []
    for line in lines[1:]:
        values = line.split("\t")
        rows.append(dict(zip(headers, values)))
    return rows


def decode_name(hex_str):
    """Decode a UTF-16LE HEX-encoded character name."""
    try:
        return bytes.fromhex(hex_str).decode("utf-16-le").rstrip("\x00").strip()
    except Exception:
        return "(decode error)"


# ---------------------------------------------------------------------------
# Routes
# ---------------------------------------------------------------------------

@app.route("/")
def index():
    chars = parse_tsv(run_sql(
        "SELECT CharID, Username, HEX(CharacterName) AS NameHex "
        "FROM pcharacter ORDER BY Username, CharID"
    ))
    for c in chars:
        c["Name"] = decode_name(c["NameHex"])
    return render_template_string(INDEX_TMPL, chars=chars)


@app.route("/forge/<int:charid>")
def forge_view(charid):
    shard = charid % 10
    table = f"charinv_{shard}"
    slots = parse_tsv(run_sql(
        f"SELECT Indx, ItemID, SlotNum, Field1, Field2, Field3, Field4, Field5 "
        f"FROM {table} WHERE CharID={charid} AND SlotNum BETWEEN 151 AND 156 "
        f"ORDER BY SlotNum"
    ))

    # Get character name for the page
    char_info = parse_tsv(run_sql(
        f"SELECT HEX(CharacterName) AS NameHex FROM pcharacter WHERE CharID={charid}"
    ))
    char_name = decode_name(char_info[0]["NameHex"]) if char_info else f"CharID {charid}"

    # Build slot info: positions 1-6 = SlotNum 151-156
    slot_map = {int(s["SlotNum"]): s for s in slots}
    display_slots = []
    for slot_num in range(151, 157):
        s = slot_map.get(slot_num)
        if s:
            display_slots.append({
                "position": slot_num - 150,
                "slot_num": slot_num,
                "indx": s["Indx"],
                "item_id": s["ItemID"],
                "is_empty": s["ItemID"] == "0",
                "field1": s["Field1"],
                "field2": s["Field2"],
                "field3": s["Field3"],
                "field4": s["Field4"],
                "field5": s["Field5"],
            })
        else:
            display_slots.append({
                "position": slot_num - 150,
                "slot_num": slot_num,
                "indx": None,
                "item_id": "0",
                "is_empty": True,
                "field1": "0", "field2": "0", "field3": "0",
                "field4": "0", "field5": "0",
            })

    # Validate forgeable: slot 1 has item, slots 2-6 have items
    weapon = display_slots[0]
    components = display_slots[1:]
    forgeable = (not weapon["is_empty"]) and all(not c["is_empty"] for c in components)

    return render_template_string(
        FORGE_TMPL,
        charid=charid,
        char_name=char_name,
        weapon=weapon,
        components=components,
        forgeable=forgeable,
        shard=shard,
    )


@app.route("/do_forge", methods=["POST"])
def do_forge():
    charid = int(request.form["charid"])
    weapon_indx = int(request.form["weapon_indx"])
    comp_indxs = [int(x) for x in request.form.getlist("comp_indx")]
    comp_itemids = [int(x) for x in request.form.getlist("comp_itemid")]

    if len(comp_itemids) != 5 or len(comp_indxs) != 5:
        return "Forge requires exactly 5 components.", 400

    shard = charid % 10
    table = f"charinv_{shard}"

    # Stamp components into the weapon row
    run_sql(
        f"UPDATE {table} SET "
        f"Field1={comp_itemids[0]}, Field2={comp_itemids[1]}, "
        f"Field3={comp_itemids[2]}, Field4={comp_itemids[3]}, "
        f"Field5={comp_itemids[4]} WHERE Indx={weapon_indx}"
    )

    # Empty the component rows (keep them as empty slots, don't delete)
    indx_list = ",".join(str(i) for i in comp_indxs)
    run_sql(
        f"UPDATE {table} SET ItemID=0, Quantity=0, Identified=0, "
        f"Field1=0, Field2=0, Field3=0, Field4=0, Field5=0, "
        f"Hardness=0, Durability=100, Level=1, XP=0 "
        f"WHERE Indx IN ({indx_list})"
    )

    return render_template_string(SUCCESS_TMPL, charid=charid)


# ---------------------------------------------------------------------------
# Templates
# ---------------------------------------------------------------------------

BASE_CSS = """
<style>
body { font-family: system-ui, sans-serif; max-width: 800px; margin: 2em auto; padding: 0 1em; }
h1 { color: #444; }
.warning { background: #fff8dc; border-left: 4px solid #d4a017; padding: 0.8em 1em; margin: 1em 0; }
.error { background: #ffe5e5; border-left: 4px solid #c00; padding: 0.8em 1em; margin: 1em 0; }
.success { background: #e6f7e6; border-left: 4px solid #2c2; padding: 0.8em 1em; margin: 1em 0; }
table { border-collapse: collapse; width: 100%; margin: 1em 0; }
th, td { border: 1px solid #ddd; padding: 0.5em; text-align: left; }
th { background: #f4f4f4; }
.empty { color: #999; font-style: italic; }
button, .btn { padding: 0.6em 1.2em; font-size: 1em; cursor: pointer; }
.btn-primary { background: #2c5aa0; color: white; border: none; border-radius: 3px; }
.btn-primary:hover { background: #1e3f7a; }
.btn-disabled { background: #ccc; color: #666; cursor: not-allowed; border: none; border-radius: 3px; padding: 0.6em 1.2em; }
select { padding: 0.4em; font-size: 1em; min-width: 300px; }
a { color: #2c5aa0; }
.label { color: #666; font-size: 0.9em; }
</style>
"""

INDEX_TMPL = """
<!doctype html>
<html><head><title>FWO Admin Forge</title>{{ css|safe }}</head>
<body>
<h1>FWO Admin Forge</h1>
<p>Sandbox tool: bypass in-game forge restrictions by directly stamping components onto inventory rows.</p>

<form action="" method="get" onsubmit="this.action='/forge/'+document.getElementById('charid').value; return true;">
  <label for="charid">Select character:</label><br>
  <select name="charid" id="charid" required>
    <option value="">-- pick --</option>
    {% for c in chars %}
      <option value="{{ c.CharID }}">{{ c.Name }} <span class="label">(account: {{ c.Username }}, ID: {{ c.CharID }})</span></option>
    {% endfor %}
  </select><br><br>
  <button class="btn btn-primary" type="submit">Continue</button>
</form>

<div class="warning">
  <strong>Workflow:</strong>
  <ol>
    <li>In-game: put weapon in action tray slot 1, components in slots 2-6</li>
    <li>Log out of the game</li>
    <li>Pick character above, click Continue, then Forge</li>
    <li>Log back in to see the forged result</li>
  </ol>
</div>
</body></html>
"""

FORGE_TMPL = """
<!doctype html>
<html><head><title>Forge - {{ char_name }}</title>{{ css|safe }}</head>
<body>
<h1>Forge for {{ char_name }}</h1>
<p><a href="/">&larr; Pick a different character</a> | <span class="label">CharID {{ charid }}, shard {{ shard }}</span></p>

<div class="warning"><strong>Make sure {{ char_name }} is logged out before forging.</strong> The engine caches inventory in memory.</div>

<h2>Action tray contents</h2>
<table>
  <tr><th>Slot</th><th>Item ID</th><th>Current components (Field1-5)</th></tr>
  <tr style="background: {{ '#fff' if weapon.is_empty else '#eef5ff' }}">
    <td><strong>1 (weapon)</strong></td>
    <td>{% if weapon.is_empty %}<span class="empty">empty</span>{% else %}{{ weapon.item_id }}{% endif %}</td>
    <td>{{ weapon.field1 }}, {{ weapon.field2 }}, {{ weapon.field3 }}, {{ weapon.field4 }}, {{ weapon.field5 }}</td>
  </tr>
  {% for c in components %}
  <tr>
    <td>{{ c.position }}</td>
    <td>{% if c.is_empty %}<span class="empty">empty</span>{% else %}{{ c.item_id }}{% endif %}</td>
    <td><span class="label">(consumed in forge)</span></td>
  </tr>
  {% endfor %}
</table>

{% if forgeable %}
<form action="/do_forge" method="post" onsubmit="return confirm('Forge {{ weapon.item_id }} with the 5 components? This stamps Field1-Field5 onto the weapon row and deletes the component rows.');">
  <input type="hidden" name="charid" value="{{ charid }}">
  <input type="hidden" name="weapon_indx" value="{{ weapon.indx }}">
  {% for c in components %}
    <input type="hidden" name="comp_indx" value="{{ c.indx }}">
    <input type="hidden" name="comp_itemid" value="{{ c.item_id }}">
  {% endfor %}
  <button class="btn btn-primary" type="submit">Forge</button>
</form>
{% else %}
<button class="btn-disabled" disabled>Forge (need weapon + 5 components in slots 1-6)</button>
{% endif %}

</body></html>
"""

SUCCESS_TMPL = """
<!doctype html>
<html><head><title>Forge Complete</title>{{ css|safe }}</head>
<body>
<h1>Forged.</h1>
<div class="success">Components stamped onto the weapon row. Component slots emptied.</div>
<p><strong>Log back in to see the forged weapon in your character's inventory.</strong></p>
<p><a href="/">&larr; Forge another character's weapon</a> | <a href="/forge/{{ charid }}">&larr; Same character again</a></p>
</body></html>
"""

# Inject CSS into all templates
for name in list(globals()):
    if name.endswith("_TMPL"):
        globals()[name] = globals()[name].replace("{{ css|safe }}", BASE_CSS)


# ---------------------------------------------------------------------------
# Run
# ---------------------------------------------------------------------------

if __name__ == "__main__":
    print("=" * 60)
    print("  FWO Admin Forge - http://localhost:5000")
    print("=" * 60)
    print("  Make sure fwo-db container is running.")
    print("  Press Ctrl+C to stop.")
    print()
    app.run(host="127.0.0.1", port=5000, debug=False)

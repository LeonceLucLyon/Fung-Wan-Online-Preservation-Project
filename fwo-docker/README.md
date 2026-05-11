# FWO Offline Preservation Project

A self-contained, single-player offline server for **Fung Wan Online**, built on Docker for Windows. All original FWO server binaries (authsys, wctrlr, zoneserver, logserver) running against the original MySQL 4.1.22 in containers.

This is a preservation project. The original game went offline; this brings it back as a sandbox single-player experience.

---

## Quick Start

### Prerequisites

1. **Docker Desktop** for Windows — [download here](https://www.docker.com/products/docker-desktop/). Install, launch it once, wait for it to finish starting.

2. **The Docker images tarball** — `BACKUP_docker_images.tar` (~270MB). Too large for GitHub, so it lives at:
   - **[Download link goes here]**

   Save it to the same folder as `install.bat` (i.e. inside the extracted project root).

### Install

1. Extract this project to a folder. `C:\fwo-docker\` is recommended.
2. Drop `BACKUP_docker_images.tar` into that folder.
3. Make sure Docker Desktop is running.
4. Double-click `install.bat`.

The installer will:
- Verify Docker is up
- Load the FWO Docker images from the tarball
- Bring up the database container
- Restore the gold-standard preservation snapshot
- Bring up the game server container

Takes ~2-3 minutes total. When it's done, launch the FWO client and connect to `127.0.0.1`.

### Accounts

100 player accounts are pre-created: `Account1` through `Account100`, all with password `fwopass`.

The admin account is `admin` with password `fwopass`. The admin account has the in-game GM character.

---

## Optional Customization

After install, run any of these to tweak the server. They prompt for a value and apply it.

| Tool | What it does |
|---|---|
| `Set_Custom_EXP_Rate.bat` | Sets NPC kill XP multiplier (1-100x). Always re-applied from baseline so rates don't compound. |
| `Set_Custom_Level_Cap.bat` | Caps maximum character level (1-221). Existing characters above the cap get reset to that level with the matching XP. |
| `fwo_admin.py` | GM-only forge tool. Runs as a local webpage. Bypasses in-game restrictions to forge any weapon with any 5 components. **Requires Python 3 — see Forge Tool section below.** |

All three require the server to be restarted after use:
```cmd
docker-compose restart fwo-server
```

---

## Daily Usage

```cmd
docker-compose up -d         REM start the server
docker-compose down          REM stop (preserves data)
docker-compose down -v       REM stop AND wipe database (full reset)
recover.bat                  REM run after a crash to unsuspend a stuck account
```

---

## Forge Tool (Optional)

`fwo_admin.py` is a local-only webpage that lets a GM directly forge weapons by modifying database records. Bypasses the engine's level/skill checks. Sandbox use only.

### One-time setup

Install Python 3 from [python.org](https://www.python.org/downloads/). **Make sure to check "Add python.exe to PATH" during install.**

Then:
```cmd
pip install flask
```

If `python` isn't recognized after install, Windows 11 has a default redirect to the Microsoft Store. Either:
- Use `py` instead of `python` (works without changes), OR
- Open Settings → App execution aliases → toggle off `python.exe` and `python3.exe`

### Running it

```cmd
cd C:\fwo-docker
python fwo_admin.py
```

Open `http://localhost:5000` in a browser. Pick character, see action tray contents, click Forge.

**Workflow:**
1. In-game: place weapon in action tray slot 1, components in slots 2-6
2. Log out of the game
3. Open the webpage, click Forge
4. Log back in to see the forged weapon

---

## Troubleshooting

**Names of other players show as old/wrong characters after a wipe**
The client caches CharID→Name mappings in `userdata\` files. After any DB wipe, delete the contents:
```cmd
del /q "<your-client-folder>\userdata\*"
```

**MOTD shows old text after a server-side change**
The client caches the MOTD in `motd.dat`. Delete it and log in fresh:
```cmd
del "<your-client-folder>\motd.dat"
```

**Account auto-suspends after a server crash**
Run `recover.bat`. Original FWO had this as a cron job; we use a manual command.

**`python` command not recognized on Windows 11**
See the Forge Tool section above.

**Docker Desktop won't start / install fails**
Ensure WSL2 is enabled, Hyper-V is enabled, and your Windows version is 10 build 19041+ or any Windows 11.

---

## Architecture

- **fwo-db**: MySQL 4.1.22 in a container. Inherits from `vettadock/mysql-old:4.1` with FWO-specific config (skip-symbolic-links, old-passwords, query log enabled). The original FWO server binaries are linked against `libmysqlclient.so.10` and only speak the pre-MySQL-4.1 password protocol — running on MySQL 5.5+ caused crashes on inventory queries.

- **fwo-server**: Linux container running the four original FWO server daemons under supervisord (authsys port 7778, wctrlr port 8888, zoneserver UDP 9999, logserver port 5961). Auto-runs idempotent maintenance scripts at startup including `auto_heal_clans.sql` which keeps clan recruiters working.

- **Database state**: Single-file `BACKUP_v14_complete.sql` includes all four FWO databases (fwsubdevdb, fwworlddevdb, fwcharlog, gmadm) with all schema modifications, MERGE tables, MOTD, unlocked items, and per-character XP baseline already baked in.

---

## Credits

- **Original FWO development team**: Choong Li Mei, Yoke Chin Dan, Terence Tan, Hafiz Awang Pon, Michael Ooi, Kenneth Chieng, Tzu Chjeh (TC) Wu, Jarod Lim, and many others.
- **Ma Wing-shing**: creator of the Fung Wan / Storm Riders IP.
- **Vettabase** for hosting `vettadock/mysql-old:4.1`.
- **Leonce Luc-Lyon** — preservation project maintainer.

---

## License & Distribution

This is a non-commercial preservation project. The original Fung Wan Online IP and Storm Riders franchise belong to their respective owners. Server binaries are redistributed under the assumption that the original game is no longer commercially available and no rights holder is enforcing distribution.

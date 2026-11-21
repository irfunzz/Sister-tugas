#!/usr/bin/env python3
import sys
import os
import time
import argparse
import subprocess
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent
PHP_CLI_SCRIPT = BASE_DIR / "backup_cli.php"
BACKUPS_DIR = BASE_DIR / "backups"

def run_php_action(action, extra_arg=None):
    if not PHP_CLI_SCRIPT.exists():
        print(f"Error: {PHP_CLI_SCRIPT} tidak ditemukan.")
        sys.exit(1)
        
    cmd = ["php", str(PHP_CLI_SCRIPT), action]
    if extra_arg:
        cmd.append(str(extra_arg))
        
    result = subprocess.run(cmd, capture_output=False)
    return result.returncode

def main():
    parser = argparse.ArgumentParser(description="Backup Storage Manager - Sistem Terdistribusi (Desktop Node)")
    subparsers = parser.add_subparsers(dest="command", help="Perintah yang tersedia")

    # Command: backup
    subparsers.add_parser("backup", help="Ambil snapshot cadangan dari Cloud Database ke Desktop PC")

    # Command: list
    subparsers.add_parser("list", help="Tampilkan daftar file cadangan lokal di desktop")

    # Command: restore
    restore_parser = subparsers.add_parser("restore", help="Pulihkan Cloud Database dari file cadangan")
    restore_parser.add_argument("filename", nargs="?", default=None, help="Nama file backup (opsional, default: terbaru)")

    # Command: daemon
    daemon_parser = subparsers.add_parser("daemon", help="Jalankan pencadangan otomatis secara berkala")
    daemon_parser.add_argument("--interval", type=int, default=60, help="Interval waktu backup otomatis dalam detik (default: 60)")

    args = parser.parse_args()

    if args.command == "backup":
        run_php_action("backup")
    elif args.command == "list":
        run_php_action("list")
    elif args.command == "restore":
        run_php_action("restore", args.filename)
    elif args.command == "daemon":
        print(f"Memulai background daemon backup (Interval: {args.interval} detik)...")
        print("Tekan Ctrl+C untuk menghentikan.")
        try:
            while True:
                run_php_action("backup")
                time.sleep(args.interval)
        except KeyboardInterrupt:
            print("\nDaemon dihentikan.")
    else:
        parser.print_help()

if __name__ == "__main__":
    main()

#!/usr/bin/env python3
"""Консольний клієнт для Online Games API (тільки стандартна бібліотека)."""

import json
import sys
import urllib.error
import urllib.parse
import urllib.request

BASE_URL = "http://localhost:8000"


def fetch(path, params=None):
    url = BASE_URL + path
    if params:
        clean = {k: v for k, v in params.items() if v}
        if clean:
            url += "?" + urllib.parse.urlencode(clean)
    try:
        with urllib.request.urlopen(url, timeout=5) as resp:
            return json.loads(resp.read().decode("utf-8"))
    except urllib.error.HTTPError as e:
        try:
            msg = json.loads(e.read().decode("utf-8")).get("error", e.reason)
        except Exception:
            msg = e.reason
        print(f"Помилка {e.code}: {msg}")
    except urllib.error.URLError:
        print(f"Не вдалося підключитися до {BASE_URL}. Чи запущено сервер?")
    return None


def print_table(games):
    if not games:
        print("Нічого не знайдено.")
        return
    print(f"{'ID':<4}{'Назва':<22}{'Жанр':<15}{'Рік':<6}{'Безкоштовна'}")
    print("-" * 60)
    for g in games:
        free = "так" if g["free_to_play"] else "ні"
        print(f"{g['id']:<4}{g['title']:<22}{g['genre']:<15}{g['release_year']:<6}{free}")


def print_game(g):
    print(f"\n{g['title']} ({g['release_year']})")
    print(f"  Жанр:         {g['genre']}")
    print(f"  Розробник:    {g['developer']}")
    print(f"  Платформи:    {', '.join(g['platforms'])}")
    print(f"  Free-to-play: {'так' if g['free_to_play'] else 'ні'}")
    print(f"  Опис:         {g['description']}")


def list_games():
    data = fetch("/api/games")
    if data:
        print_table(data["data"])


def show_game():
    gid = input("Введіть ID гри: ").strip()
    if not gid.isdigit():
        print("ID має бути числом.")
        return
    game = fetch(f"/api/games/{gid}")
    if game:
        print_game(game)


def by_genre():
    genres = fetch("/api/genres")
    if not genres:
        return
    print("Жанри:", ", ".join(genres))
    genre = input("Оберіть жанр: ").strip()
    data = fetch("/api/games", {"genre": genre})
    if data:
        print_table(data["data"])


def search():
    q = input("Пошук за назвою: ").strip()
    data = fetch("/api/games", {"search": q})
    if data:
        print_table(data["data"])


def free_games():
    data = fetch("/api/games", {"free": "true"})
    if data:
        print_table(data["data"])


MENU = {
    "1": ("Усі ігри", list_games),
    "2": ("Інформація про гру за ID", show_game),
    "3": ("Фільтр за жанром", by_genre),
    "4": ("Пошук за назвою", search),
    "5": ("Тільки безкоштовні", free_games),
    "0": ("Вихід", None),
}


def main():
    global BASE_URL
    if len(sys.argv) > 1:
        BASE_URL = sys.argv[1].rstrip("/")
    while True:
        print("\n=== Online Games API ===")
        for key, (title, _) in MENU.items():
            print(f"{key}. {title}")
        try:
            choice = input("Ваш вибір: ").strip()
        except EOFError:
            break
        if choice == "0":
            print("До побачення!")
            break
        action = MENU.get(choice)
        if action:
            action[1]()
        else:
            print("Невірний пункт меню.")


if __name__ == "__main__":
    main()

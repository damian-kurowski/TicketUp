# Ticketup - System sprzedaży biletów online

[![Symfony](https://img.shields.io/badge/Symfony-5.11-blue)](https://symfony.com/)
[![PHP](https://img.shields.io/badge/PHP-8.4-green)](https://www.php.net/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-purple)](https://www.postgresql.org/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.x-orange)](https://getbootstrap.com/)

**Ticketup** to nowoczesna platforma do zakupu biletów na wydarzenia online. Pozwala użytkownikom przeglądać nadchodzące wydarzenia, rejestrować się, logować oraz kupować bilety w intuicyjny sposób.

---

## Spis treści

1. [Opis projektu](#opis-projektu)
2. [Funkcje główne](#funkcje-główne)
3. [Technologie użyte w projekcie](#technologie-użyte-w-projekcie)
4. [Instalacja i uruchomienie](#instalacja-i-uruchomienie)
5. [Konfiguracja](#konfiguracja)
6. [Struktura projektu](#struktura-projektu)
7. [Autor](#autor)
8. [Licencja](#licencja)

---

## Opis projektu

Ticketup to system umożliwiający użytkownikom:
- Rejestrację i logowanie za pomocą adresu e-mail.
- Przeglądanie nadchodzących wydarzeń.
- Dodawanie biletów do koszyka (w trakcie implementacji).
- Bezpieczne zakupy dzięki uwierzytelnianiu Symfony Security.

Projekt został stworzony z myślą o prostocie użytkowania i skalowalności.

---

## Funkcje główne

- **Rejestracja i logowanie**:
    - Użytkownicy mogą tworzyć konta i logować się przy użyciu adresu e-mail.
    - Wsparcie dla CSRF i bezpiecznego hashowania haseł.
- **Wyświetlanie wydarzeń**:
    - Lista nadchodzących wydarzeń z opisami, datami i obrazami.
- **Responsywny design**:
    - Interfejs oparty na Bootstrap 5, dostosowany do urządzeń mobilnych.
- **Bezpieczeństwo**:
    - Implementacja zapory sieciowej i kontroli dostępu w Symfony.

---

## Technologie użyte w projekcie

- **Backend**: Symfony 5.11
- **Frontend**: HTML5, CSS3, Bootstrap 5
- **Baza danych**: Doctrine ORM, PostgreSQL 16
- **Bezpieczeństwo**: Symfony Security, CSRF Protection
- **Inne**: Twig, PHP 8.4

---

## Instalacja i uruchomienie

### Wymagania

Przed rozpoczęciem upewnij się, że masz zainstalowane:
- PHP >= 8.4
- Composer
- PostgreSQL 16
- Node.js (opcjonalnie, do zarządzania zależnościami frontendowymi)

### Kroki instalacji

1. **Sklonuj repozytorium**:
   ```bash
   git clone https://github.com/damian-kurowski/TicketUp.git
   cd ticketup
   ```

2. **Zainstaluj zależności PHP**:
   ```bash
   composer install
   ```

3. **Skonfiguruj plik `.env`**:
   Skopiuj plik `.env` do `.env.local` i ustaw parametry połączenia z bazą danych:
   ```env
   DATABASE_URL="postgresql://db_user:db_password@127.0.0.1:5432/db_name?serverVersion=16&charset=utf8"
   ```

4. **Utwórz bazę danych**:
   ```bash
   php bin/console doctrine:database:create
   php bin/console make:migration
   php bin/console doctrine:migrations:migrate
   ```

5. **Uruchom serwer deweloperski**:
   ```bash
   symfony server:start
   ```

6. **Otwórz projekt w przeglądarce**:
   Przejdź pod adres `http://localhost:8000`.

---

## Konfiguracja

### Zmienne środowiskowe

- `DATABASE_URL`: Parametry połączenia z bazą danych.
- `APP_ENV`: Środowisko aplikacji (`dev` lub `prod`).
- `APP_SECRET`: Klucz tajny aplikacji (generowany automatycznie).

### Role użytkowników

- `ROLE_USER`: Dostęp do zakupu biletów.
- `ROLE_ADMIN`: Dostęp do panelu administracyjnego (w trakcie implementacji).

---

## Struktura projektu

```
ticketup/
├── assets/            # Pliki statyczne (CSS, JS)
├── config/            # Konfiguracja Symfony
│   ├── packages/      # Konfiguracja pakietów (security.yaml, twig.yaml itp.)
│   └── routes/        # Routing
├── migrations/        # Migracje Doctrine
├── public/            # Pliki publiczne (Bootstrap, obrazy)
├── src/               # Kod źródłowy
│   ├── Controller/    # Kontrolery
│   ├── Entity/        # Encje Doctrine
│   └── Repository/    # Repozytoria
├── templates/         # Szablony Twig
└── var/               # Logi i pamięć podręczna
```

---

## Autor

- **Damian Kurowski**
    - E-mail: kurowsski@gmail.com

Jeśli masz pytania lub sugestie, napisz do mnie!

---

## Licencja

Ten projekt jest dostępny na licencji MIT.
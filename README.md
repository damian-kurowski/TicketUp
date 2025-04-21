# TicketUp - System Rezerwacji Biletów Online

**Autor:** Damian Kurowski
**Data utworzenia:** 21.04.2025

## Opis Projektu

TicketUp to aplikacja webowa napisana w frameworku Symfony, służąca jako system do sprzedaży i rezerwacji biletów online na różnego rodzaju wydarzenia (koncerty, teatr, sport, konferencje itp.). Projekt został stworzony w celach edukacyjnych jako projekt studencki. Aplikacja umożliwia przeglądanie wydarzeń, dodawanie biletów do koszyka, składanie zamówień oraz zarządzanie wydarzeniami i zamówieniami przez panel administratora.

## Główne Funkcjonalności

**Dla Użytkownika:**

* Przeglądanie listy nadchodzących wydarzeń.
* Wyszukiwanie wydarzeń po nazwie.
* Wyświetlanie szczegółów wydarzenia (opis, data, miejsce, obrazek).
* Wyświetlanie dostępnych typów biletów i ich cen.
* Dodawanie biletów do koszyka (dynamicznie, bez przeładowania strony - AJAX/Turbo).
* Przeglądanie i modyfikacja zawartości koszyka (dynamicznie dla usuwania/zmniejszania ilości - AJAX/Turbo).
* Proces składania zamówienia (Checkout) z formularzem danych klienta.
* Symulacja płatności.
* Rejestracja i logowanie użytkowników.
* Weryfikacja adresu email po rejestracji (symulowana lokalnie przez Profiler).
* Profil użytkownika z historią złożonych zamówień.
* Automatycznie znikające komunikaty informacyjne (Flash Messages).
* Dynamiczne liczniki czasu do rozpoczęcia wydarzeń.
* Responsywny design (Bootstrap 5).

**Dla Administratora (Panel Admina):**

* Zarządzanie wydarzeniami (CRUD - Tworzenie, Odczyt, Aktualizacja, Usuwanie).
* Upload obrazków dla wydarzeń.
* Zarządzanie typami biletów przypisanymi do konkretnych wydarzeń (CRUD).
* Przeglądanie listy złożonych zamówień.
* Wyświetlanie szczegółów zamówień (w tym pozycji zamówienia).
* Dostęp do panelu ograniczony dla użytkowników z rolą `ROLE_ADMIN`.
* Rozwijane menu administracyjne w głównym pasku nawigacji.

## Użyte Technologie

* **Backend:**
    * PHP (zalecana wersja 8.1+)
    * Symfony Framework (np. 6.x lub nowszy LTS)
    * Doctrine ORM (zarządzanie bazą danych)
    * Symfony Messenger (do obsługi zadań po opłaceniu zamówienia - skonfigurowany synchronicznie)
    * Twig (system szablonów)
* **Frontend:**
    * HTML5 / CSS3 / JavaScript (ES6+)
    * Bootstrap 5 (framework CSS)
    * Sass/SCSS (preprocesor CSS)
    * Webpack Encore (kompilacja i zarządzanie zasobami frontendowymi)
    * Symfony UX Turbo (do dynamicznych aktualizacji bez przeładowania strony)
    * Symfony Stimulus Bundle (framework JS, minimalne użycie w tym projekcie)
* **Baza Danych:**
    * PostgreSQL (wersja 15 lub kompatybilna)
* **Generowanie PDF:**
    * `knplabs/knp-snappy-bundle`
    * `wkhtmltopdf` (wymagana zewnętrzna instalacja)
* **Inne Narzędzia:**
    * Composer (manager zależności PHP)
    * npm / yarn (manager zależności Node.js)
    * Symfony CLI (narzędzie pomocnicze Symfony)
    * Git (system kontroli wersji)

## Instalacja i Uruchomienie (Lokalnie)

1.  **Sklonuj repozytorium:**
    ```bash
    git clone git@github.com:damian-kurowski/TicketUp.git
    cd ticketup
    ```
2.  **Zainstaluj zależności PHP:**
    ```bash
    composer install
    ```
3.  **Zainstaluj zależności Node.js:**
    ```bash
    npm install
    # lub
    # yarn install
    ```
4.  **Zbuduj zasoby frontendowe:**
    ```bash
    npm run build
    # lub do pracy deweloperskiej:
    # npm run watch
    ```
5.  **Skonfiguruj połączenie z bazą danych:**
    * Skopiuj plik `.env` do `.env.local`.
    * W pliku `.env.local` ustaw poprawny `DATABASE_URL` dla Twojej lokalnej instancji PostgreSQL (podając użytkownika, hasło, nazwę bazy - np. `ticketup`).
    * Przykład: `DATABASE_URL="postgresql://user:password@127.0.0.1:5432/ticketup?serverVersion=15&charset=utf8"`
6.  **Skonfiguruj inne zmienne środowiskowe (w `.env.local`):**
    * Upewnij się, że masz:
        ```dotenv
        APP_ENV=dev
        APP_SECRET=<WYGENERUJ_JAKIS_SEKRET> # np. za pomocą openssl rand -hex 16
        MESSENGER_TRANSPORT_DSN=sync://
        MAILER_DSN=null://null
        ```
7.  **Stwórz bazę danych (jeśli nie istnieje):**
    ```bash
    symfony console doctrine:database:create --if-not-exists
    ```
8.  **Uruchom migracje:**
    ```bash
    symfony console doctrine:migrations:migrate
    ```
9.  **Zainstaluj `wkhtmltopdf`:**
    * Pobierz i zainstaluj odpowiednią wersję dla Twojego systemu z [wkhtmltopdf.org](https://wkhtmltopdf.org/downloads.html).
    * Upewnij się, że plik wykonywalny jest w PATH systemowym lub podaj poprawną ścieżkę w `config/packages/knp_snappy.yaml`.
10. **(Opcjonalnie) Załaduj dane testowe:**
    * Użyj wygenerowanych poleceń SQL lub panelu admina, aby dodać wydarzenia i typy biletów.
11. **Utwórz katalog do uploadu:**
    * Stwórz ręcznie katalog `public/uploads/events/`.
12. **Uruchom serwer deweloperski Symfony:**
    ```bash
    symfony server:start -d
    # lub bez flagi -d, aby widzieć logi w konsoli
    # symfony server:start
    ```
13. **Otwórz aplikację w przeglądarce** (zazwyczaj `https://127.0.0.1:8000`).

## Użycie

* **Użytkownik:** Przeglądaj wydarzenia na stronie głównej, użyj wyszukiwarki, kliknij "Szczegóły i bilety", dodaj bilety do koszyka, przejdź do kasy, wypełnij dane, zasymuluj płatność. Zarejestruj się i zaloguj, aby zobaczyć profil i historię zamówień.
* **Administrator:** Zaloguj się na konto z rolą `ROLE_ADMIN` (możesz ją nadać ręcznie w bazie danych w tabeli `user`, kolumna `roles` np. `["ROLE_USER", "ROLE_ADMIN"]`). W menu nawigacyjnym pojawi się "Admin Panel" z dostępem do zarządzania wydarzeniami (dodawanie, edycja, upload zdjęć), typami biletów (w szczegółach wydarzenia) i przeglądania zamówień.

## Możliwe Dalsze Rozszerzenia

* Integracja z prawdziwą bramką płatności.
* Generowanie i obsługa kodów QR na biletach.
* Faktyczna symulacja skanowania biletów.
* Paginacja list.
* Filtrowanie wydarzeń po kategoriach/datach/lokalizacji.
* System kategorii wydarzeń.
* Resetowanie hasła.
* Dashboard dla administratora.
* Testy automatyczne (jednostkowe, integracyjne, funkcjonalne).
* Użycie VichUploaderBundle do zarządzania plikami.
# Demonstracija testiranja TODO aplikacije

Ova dokumentacija pokriva sve aspekte testiranja TODO aplikacije, uključujući postavljanje okruženja, izvršavanje testova i analizu pokrivenosti koda.

## Sadržaj
1. [Priprema okruženja](#priprema-okruženja)
2. [Vrste testova](#vrste-testova)
3. [Pokretanje testova](#pokretanje-testova)
4. [Analiza pokrivenosti koda](#analiza-pokrivenosti-koda)
5. [Budući koraci](#budući-koraci)

## Priprema okruženja

Ove upute će vam pomoći da uspješno pokažete rad testova na bilo kojem računalu s XAMPP-om.

## Priprema okruženja

1. **Instalirajte XAMPP**
   - Preuzmite i instalirajte XAMPP s [službene stranice](https://www.apachefriends.org/)
   - Tijekom instalacije odaberite barem Apache i MySQL

2. **Pokrenite potrebne servise**
   - Otvorite XAMPP Control Panel
   - Pokrenite Apache i MySQL

3. **Postavite bazu podataka**
   - Otvorite http://localhost/phpmyadmin u pregledniku
   - Kreirajte novu bazu podataka: `todo_app_test`
   - Uvezite `tests/schema.sql` u novu bazu

## Vrste testova

### 1. Statička analiza
- Provjera sintakse i stilskih grešaka
- Korišteni alati: PHP_CodeSniffer, PHPStan

### 2. Jedinični testovi
- Testiranje pojedinačnih komponenti u izolaciji
- Pokrivene komponente:
  - Korisnički model
  - Model zadataka
  - Autentifikacijska logika

### 3. Integracijski testovi
- Testiranje interakcije između komponenti
- Pokriveni slučajevi korištenja:
  - Registracija i prijava korisnika
  - Upravljanje zadacima
  - Filtriranje i sortiranje zadataka

### 4. Testovi sustava (E2E)
- Testiranje cijelog toka rada aplikacije
- Korišteni alati: Cypress

## Pokretanje testova

### Kroz web preglednik (preporučeno)
1. Otvorite http://localhost/todo_app/run_tests.php
2. Pregledajte rezultate testova

### Kroz terminal
```bash
# Pokreni sve testove
cd c:\xampp\htdocs\todo_app
c:\xampp\php\php.exe run_tests.php

# Pokreni specifične testove
c:\xampp\php\php.exe tests/Unit/UserModelTest.php
c:\xampp\php\php.exe tests/Integration/LoginTest.php
```

## Analiza pokrivenosti koda
- Cilj: Minimalna pokrivenost od 80%
- Trenutna pokrivenost: [X]%
- Izvještaj o pokrivenosti dostupan u direktoriju `coverage/`

## Budući koraci
- [ ] Povećanje pokrivenosti testovima
- [ ] Dodavanje još E2E testova
- [ ] Implementacija CI/CD pipeline-a
- [ ] Automatsko generiranje dokumentacije

## Tehnički zahtjevi

### Backend
- PHP 7.4+
- MySQL 5.7+
- PDO ekstenzija

### Frontend
- Moderni web preglednik (Chrome, Firefox, Edge)
- JavaScript omogućen

### Alati za testiranje
- PHPUnit za PHP testove
- Cypress za E2E testove
- PHP_CodeSniffer za provjeru stilova
- PHPStan za statičku analizu

## Rješavanje problema

### Uobičajeni problemi
1. **Nedostajuće ekstenzije**
   - Provjerite da su uključene sljedeće ekstenzije u `php.ini`:
     ```
     extension=mysqli
     extension=pdo_mysql
     extension=mbstring
     extension=openssl
     ```
   - Provjerite da je `extension_dir` postavljen na ispravnu putanju

2. **Greške s bazom podataka**
   - Provjerite je li MySQL pokrenut
   - Provjerite pristupne podatke u konfiguraciji

### Dijagnostika
Za detaljniju dijagnostiku, pokrenite:
```bash
c:\xampp\php\php.exe -m  # Prikaz učitavanih modula
c:\xampp\php\php.exe -i | findstr "extension_dir"  # Prikaz direktorija s ekstenzijama
```

3. **Test operacija sa zadacima**
   - Dodavanje novog zadatka
   - Ažuriranje statusa zadatka

## U slučaju grešaka

Ako se pojave greške s ekstenzijama:
1. Provjerite da su u `php.ini` ispravne putanje do ekstenzija
2. Provjerite da su otkomentirane ove linije u `php.ini`:
   ```
   extension=mysqli
   extension=pdo_mysql
   extension=mbstring
   extension=openssl
   ```
3. Provjerite da se u `php.ini` nalazi linija:
   ```
   extension_dir = "C:\xampp\php\ext"
   ```

## Napomene za demonstraciju

- Testovi su napisani na hrvatskom jeziku
- Svi testovi su automatski samoopisujući
- Nakon svakog testa, baza se vraća u početno stanje

## Dodatne informacije

- **Autori testova**: [Vaše ime]
- **Datum**: 12.06.2025.
- **Verzija aplikacije**: 1.0.0

# GespidiEffe — Gestione e modifica file PDF

[![Framework](https://img.shields.io/static/v1?label=Framework&message=Laravel%209.x&color=red&style=for-the-badge&logo=laravel)](https://laravel.com)
[![PHP Version](https://img.shields.io/static/v1?label=PHP%20Version&message=8.1&color=777BB4&style=for-the-badge&logo=php)](https://php.net)
[![License](https://img.shields.io/static/v1?label=License&message=MIT&color=green&style=for-the-badge)](https://opensource.org/licenses/MIT)

GesPidieffe è un package Laravel per la gestione e la modifica di file PDF direttamente dal browser.
Fa parte dell'ecosistema **AdmEL** ed è progettato per essere integrato come package locale in un'applicazione Laravel 9.

---

## Funzionalità

| Funzione | Descrizione |
|---|---|
| **Censura PDF** | Disegna rettangoli neri su aree sensibili del documento; supporto ibrido vettoriale/raster |
| **Unione PDF (Merge)** | Carica più file PDF, riordinali con drag & drop, uniscili in un unico documento |
| **Divisione PDF (Split)** | Estrae singole pagine o intervalli; download del singolo PDF o di uno ZIP con tutti i file estratti |
| **Organizza pagine** | Griglia drag & drop per riordinare, duplicare o eliminare singole pagine |
| **Ruota pagine** | Ruota singole pagine o l'intero documento a passi di 90°; badge visivo con i gradi correnti |
| **Numerazione pagine** | Aggiunge numeri di pagina con posizione, font, dimensione e colore personalizzabili; testo originale rimane selezionabile |

---

## Requisiti

### Applicazione host

| Requisito | Versione |
|---|---|
| PHP | ^8.0 |
| Laravel | 9.x |
| Laravel Jetstream | ^2.14 |
| Laravel Sanctum | ^3.0 |

### Dipendenze PHP (Composer)

| Package | Versione |
|---|---|
| `setasign/fpdi` | ^2.3 |
| `tecnickcom/tcpdf` | ^6.5 |

### Dipendenze di sistema (nel container Docker)

I seguenti strumenti devono essere presenti nel container PHP:

| Strumento | Utilizzo |
|---|---|
| `qpdf` | Merge, split, rotazione, conteggio pagine, estrazione in formato JSON |
| `ghostscript` (`gs`) | Rasterizzazione di pagine PDF in PNG (per la censura raster) |
| `pdftk` | Applicazione overlay di numerazione pagine (`multistamp`) |

Nel Dockerfile (Laravel Sail, runtime PHP 8.1) questi pacchetti vengono installati con:

```dockerfile
apt-get install -y ghostscript qpdf pdftk
```

---

## Ambiente di sviluppo

| Voce | Valore |
|---|---|
| Sistema operativo | WSL Ubuntu 18.04 su Windows 10 Home |
| Shell | bash (sintassi Unix) |
| PHP | 8.1 |
| Framework | Laravel 9 |
| Frontend | Tailwind CSS, Alpine.js, Vite 4 |
| Database | MariaDB 10 (Docker/Sail) |
| Cache | Redis (Docker/Sail) |
| App URL | http://localhost |
| Adminer | http://localhost:8087 |
| Vite dev server | porta 5174 |

---

## Installazione

### 1. Aggiungere il package come dipendenza locale

Nel `composer.json` dell'applicazione host, aggiungere il namespace PSR-4 nella sezione `autoload`:

```json
"autoload": {
    "psr-4": {
        "Elamacchia\\Gespidieffe\\": "package/gespidieffe/src/"
    }
}
```

Quindi copiare la cartella `package/gespidieffe/` nella propria applicazione e rigenerare l'autoload:

```bash
composer dump-autoload
```

### 2. Registrare il Service Provider

In `config/app.php`, aggiungere nella sezione `providers`:

```php
Elamacchia\Gespidieffe\GespidieffeServiceProvider::class,
```

> Il package supporta anche l'auto-discovery tramite il campo `extra.laravel.providers` nel proprio `composer.json`.

### 3. Verificare le dipendenze di sistema

Assicurarsi che `qpdf`, `ghostscript` e `pdftk` siano installati nel container (vedi sezione *Requisiti*).

### 4. Creare la cartella per i file temporanei

```bash
mkdir -p storage/app/gespidieffe/tmp
```

La cartella viene usata per salvare i PDF caricati e i file elaborati durante le sessioni utente.

### 5. Configurare lo scheduler (pulizia automatica)

GespidiEffe registra automaticamente un task nello scheduler di Laravel che rimuove ogni ora i file temporanei più vecchi di 24 ore.
Assicurarsi che il cron di Laravel sia attivo sul server:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## Comandi Artisan

| Comando | Descrizione |
|---|---|
| `php artisan gespidieffe:pulisci-tmp` | Elimina i file temporanei più vecchi di 24 ore (default) |
| `php artisan gespidieffe:pulisci-tmp --ore=48` | Elimina i file più vecchi di N ore |

---

## Route

Tutte le route sono protette dal middleware `['web', 'auth:sanctum', 'verified']`: solo gli utenti autenticati e verificati possono accedere. I guest vengono reindirizzati automaticamente al login da Sanctum/Jetstream.

Prefix URI: `/gespidieffe` — Named prefix: `gespidieffe.`

| Metodo | URI | Nome route |
|---|---|---|
| GET | `/gespidieffe/` | `gespidieffe.home` |
| GET | `/gespidieffe/censura` | `gespidieffe.censura` |
| POST | `/gespidieffe/censura/upload` | `gespidieffe.censura.upload` |
| GET | `/gespidieffe/censura/editor/{file}` | `gespidieffe.censura.editor` |
| POST | `/gespidieffe/censura/applica` | `gespidieffe.censura.applica` |
| GET | `/gespidieffe/censura/download/{file}` | `gespidieffe.censura.download` |
| DELETE/POST | `/gespidieffe/censura/elimina/{file}` | `gespidieffe.censura.elimina` |
| GET | `/gespidieffe/censura/pdf/{file}` | `gespidieffe.censura.pdf` |
| GET | `/gespidieffe/merge` | `gespidieffe.merge` |
| POST | `/gespidieffe/merge/upload` | `gespidieffe.merge.upload` |
| GET | `/gespidieffe/merge/editor/{session}` | `gespidieffe.merge.editor` |
| GET | `/gespidieffe/merge/aggiungi/{session}` | `gespidieffe.merge.aggiungi` |
| POST | `/gespidieffe/merge/applica` | `gespidieffe.merge.applica` |
| GET | `/gespidieffe/merge/download/{file}` | `gespidieffe.merge.download` |
| DELETE/POST | `/gespidieffe/merge/elimina/{session}` | `gespidieffe.merge.elimina` |
| GET | `/gespidieffe/merge/pdf/{session}/{index}` | `gespidieffe.merge.pdf` |
| GET | `/gespidieffe/split` | `gespidieffe.split` |
| POST | `/gespidieffe/split/upload` | `gespidieffe.split.upload` |
| GET | `/gespidieffe/split/editor/{file}` | `gespidieffe.split.editor` |
| POST | `/gespidieffe/split/applica` | `gespidieffe.split.applica` |
| GET | `/gespidieffe/split/download/{file}` | `gespidieffe.split.download` |
| GET | `/gespidieffe/split/download-zip/{file}` | `gespidieffe.split.download-zip` |
| DELETE/POST | `/gespidieffe/split/elimina/{file}` | `gespidieffe.split.elimina` |
| GET | `/gespidieffe/split/pdf/{file}` | `gespidieffe.split.pdf` |
| GET | `/gespidieffe/organizza` | `gespidieffe.organizza` |
| POST | `/gespidieffe/organizza/upload` | `gespidieffe.organizza.upload` |
| GET | `/gespidieffe/organizza/editor/{file}` | `gespidieffe.organizza.editor` |
| POST | `/gespidieffe/organizza/applica` | `gespidieffe.organizza.applica` |
| GET | `/gespidieffe/organizza/download/{file}` | `gespidieffe.organizza.download` |
| DELETE/POST | `/gespidieffe/organizza/elimina/{file}` | `gespidieffe.organizza.elimina` |
| GET | `/gespidieffe/organizza/pdf/{file}` | `gespidieffe.organizza.pdf` |
| GET | `/gespidieffe/ruota` | `gespidieffe.ruota` |
| POST | `/gespidieffe/ruota/upload` | `gespidieffe.ruota.upload` |
| GET | `/gespidieffe/ruota/editor/{file}` | `gespidieffe.ruota.editor` |
| POST | `/gespidieffe/ruota/applica` | `gespidieffe.ruota.applica` |
| GET | `/gespidieffe/ruota/download/{file}` | `gespidieffe.ruota.download` |
| DELETE/POST | `/gespidieffe/ruota/elimina/{file}` | `gespidieffe.ruota.elimina` |
| GET | `/gespidieffe/ruota/pdf/{file}` | `gespidieffe.ruota.pdf` |
| GET | `/gespidieffe/numera` | `gespidieffe.numera` |
| POST | `/gespidieffe/numera/upload` | `gespidieffe.numera.upload` |
| GET | `/gespidieffe/numera/editor/{file}` | `gespidieffe.numera.editor` |
| POST | `/gespidieffe/numera/applica` | `gespidieffe.numera.applica` |
| GET | `/gespidieffe/numera/download/{file}` | `gespidieffe.numera.download` |
| DELETE/POST | `/gespidieffe/numera/elimina/{file}` | `gespidieffe.numera.elimina` |
| GET | `/gespidieffe/numera/pdf/{file}` | `gespidieffe.numera.pdf` |

---

## Architettura: flusso multi-step

Ogni funzione segue questo schema a cinque fasi:

```
1. Upload      → validazione → salva in storage/app/gespidieffe/tmp/{uuid}.pdf
                              → redirect all'editor

2. Editor      → anteprima pagine con PDF.js (client-side)
                              → Alpine.js per interazione utente

3. Elaborazione → POST con parametri operazione
                              → manipolazione server-side (qpdf / Ghostscript / TCPDF / pdftk)
                              → risposta JSON con { download_token }

4. Download    → GET con token → response()->download()

5. Pulizia     → DELETE/POST elimina file della sessione
                              → scheduler rimuove automaticamente file > 24h
```

### File temporanei

- Cartella: `storage/app/gespidieffe/tmp/`
- Naming: `{uuid}.pdf` (originale), `{uuid}_<suffisso>.pdf` (output elaborato)

---

## Struttura del package

```
package/gespidieffe/
├── composer.json
└── src/
    ├── GespidieffeServiceProvider.php
    ├── Console/
    │   └── PulisciTmpCommand.php
    ├── Http/Controllers/
    │   ├── GespidieffeController.php
    │   ├── CensuraPdfController.php
    │   ├── MergePdfController.php
    │   ├── SplitPdfController.php
    │   ├── OrganizzaPdfController.php
    │   ├── RuotaPdfController.php
    │   └── NumeraPdfController.php
    ├── routes/
    │   └── web.php
    └── resources/views/
        ├── layouts/app.blade.php
        ├── home.blade.php
        ├── censura/
        │   ├── upload.blade.php
        │   └── editor.blade.php
        ├── merge/
        │   ├── upload.blade.php
        │   └── editor.blade.php
        ├── split/
        │   ├── upload.blade.php
        │   └── editor.blade.php
        ├── organizza/
        │   ├── upload.blade.php
        │   └── editor.blade.php
        ├── ruota/
        │   ├── upload.blade.php
        │   └── editor.blade.php
        └── numera/
            ├── upload.blade.php
            └── editor.blade.php
```

---

## Identificativi

| Voce | Valore |
|---|---|
| Nome Composer | `elamacchia/gespidieffe` |
| Namespace PHP | `Elamacchia\Gespidieffe\` |
| Namespace view | `gespidieffe::` |
| Service Provider | `Elamacchia\Gespidieffe\GespidieffeServiceProvider` |
| Licenza | MIT |
| Autore | Enzo Lamacchia — e.lamacchia@gmail.com |

---

## Licenza

Distribuito sotto licenza [MIT](https://opensource.org/licenses/MIT).
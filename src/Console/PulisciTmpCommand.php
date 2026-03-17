<?php

namespace Elamacchia\Gespidieffe\Console;

use Illuminate\Console\Command;

class PulisciTmpCommand extends Command
{
    protected $signature   = 'gespidieffe:pulisci-tmp {--ore=24 : Elimina file più vecchi di N ore}';
    protected $description = 'Elimina i file temporanei di GespidiEffe più vecchi del limite specificato';

    public function handle(): int
    {
        $ore  = (int) $this->option('ore');
        $base = storage_path('app/gespidieffe/tmp');

        if (!is_dir($base)) {
            $this->info('Cartella tmp non trovata, niente da fare.');
            return self::SUCCESS;
        }

        $limite    = time() - ($ore * 3600);
        $eliminati = 0;

        foreach (glob($base . '/*') as $file) {
            if (is_file($file) && filemtime($file) < $limite) {
                @unlink($file);
                $eliminati++;
            }
        }

        $this->info("GespidiEffe tmp: eliminati {$eliminati} file più vecchi di {$ore} ore.");

        return self::SUCCESS;
    }
}

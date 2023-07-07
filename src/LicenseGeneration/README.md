# License generation

Génération de licenses signées.

## Description

Permet de générer de nouvelles licenses liées à des sociétés.

## Configuration

Nécessite une clé privée pour signer les licenses, et la clé publique associée
qui devra être mise en publique, par exemple dans `public/license/public-key.pem`.

Configuration `.env`:

```
LICENSE_GENERATION_PRIVATE_KEY=%kernel.project_dir%/var/license/private.pem
LICENSE_GENERATION_PUBLIC_KEY=%kernel.project_dir%/public/license/public-key.pem
```

Commandes :

``` bash
app:license-generation:generate-private
# Pour pouvoir générer des licenses : génère une paire de clés privée/publique.

app:license-generation:generate:unlimited
# Pour le développement : génère des licenses illimitées pour chaque société.
```

## Technique

- Dépends du composant `License`

Le service `App\LicenseGeneration\LicenseGeneration` permet de générer
une license à partir d'une instance de `App\License\DTO\License`.

Ce contenu pourra être enregistré dans un fichier et envoyé à un client.

Exemple de génération d'une license :

``` php
use App\License\DTO\License;
use App\License\LicenseService;
use App\LicenseGeneration\LicenseGeneration;

// Créer une instance de License
$license = new License();
$license->setQuotas([/* ... */]);

// Signe une license
$licenseContent = $licenseGeneration->generateLicenseFile($license);

// Stocke le contenu dans un fichier et le place dans le bon dossier
// (var/storage/licenses/{societe_uuid}/rdi-manager-license.txt)
$licenseService->storeLicense($licenseContent);
```

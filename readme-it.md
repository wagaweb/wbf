#Waboot Framework

WBF è un framework modulare per Wordpress che permette di accellerare i tempi di sviluppo di funzionalità per temi e plugin.

E' possibile utilizzare WBF includendo all'interno della propria applicazione, oppure installandolo come plugin. In quest'ultimo caso rende accessibile agli sviluppatori tutta una serie di funzionalità aggiuntive da implementare all'interno dei temi.

##WBF come plugin

Installando WBF come plugin lo sviluppatore può implementare una serie di funzionalità all'interno del tema:

- Gestione delle Theme Options e delle metabox ad esse collegate (Behavior)
- Gestione dei componenti (microfunzionalità a gestione separata)
- Gestione di eventuali processi di aggiornamento personalizzati
- Gestione delle licenze
- Gestione della compilazione live di stili LESS
- Rende disponibili delle estensioni per plugin comuni (come acf e woocommerce)
- Rende disponibili alcune librerie javascript aggiuntive (in particolare WBFGmap che rende più rapida l'implementazione delle mappe di google)

##WBF come framework

WBF è un framework composto da diversi componenti utilizzabili separatamente sia all'interno di temi che all'interno di plugin. I componenti disponibili sono:

- Assets
Comprende degli strumenti per la gestione degli asset (stili e javascript).

Rende possibile registrare gli assets tramite un AssetsManager, il quale si occupa di:

- Controllare l'esistenza effettiva dell'asset e notificarne l'eventuale mancanza
- Assegnargli un numero di versione basato sull'ultima versione del file (al fine di una migliore compatibilità con sistemi di cache)
- Accodare l'asset durante l'esecuzione di Wordpress

Permette inoltre di eseguire tutta una serie di operazione aggiuntive sugli asset, documentate all'interno del componente.

- Breadcrumb
Rende disponibile una serie di strumenti per la gestione del breadcrumb.

- Compiler
Permette di gestire la compilazione live di stili LESS o la generazione di file CSS a partire da template. Utile da abbinare alle Theme Options.

- CustomUpdater
Permette di gestire server di update personalizzati per plugin e temi

- License
Permette la gestione di sistemi di licenze per plugin e temi

- MVC
Rende possibile utilizzare un approccio MVC all'interno delle template per WordPress e la implementazione di sistemi di templating personalizzati.

- NavWalker
Mette a disposizione una serie di navwalker per i menu di Wordpress. Al momento è disponibile un navwalker compatibile con bootstrap.

- Notices
Rende possibile l'utilizzo di un sistema di notifiche centralizzato per la dashboard.

- Plugins Framework
Rende disponibili una serie di funzionalità per lo sviluppo rapido di plugin. In particolare:

- Permette un approccio "conventions over configuration" allo sviluppo di plugin: strutturando la plugin in una maniera standard e consolidata il framework solleverà lo sviluppatore da tutta una serie di compiti ripetitivi.
- Rende rapida la creazione di plugin che incorporano template sovrascribili dai temi
- Rende possibile la comunicazione tra plugin diverse

- Utils
Rende disponibili una serie di funzioni di utility per la velocizzazione dei processi produttivi.

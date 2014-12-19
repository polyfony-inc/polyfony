Ce qui n'est pas parfait sur MVC
- absence de support pour bundles/plugins, se rapprocher de notre framework pour ça
- absence de support pour locales, reprendre pfLocalization pour ça, en chargement dynamique
- usage des namespaces (c'est joli et laid à la fois), préfixer par pf à la place
- la classe de requête SQL a une syntaxe trop lourde, reprendre pfQuery à la place
- l'inclusion initiale depuis Web/index.php n'est pas parfaite voir le commit initial avec pfLoader
- le routage, il y'a du bon et du moins bon, trouver un compromis légereté/flexibilité
- le rangement de certaines données/fichiers, se rapprocher de notre framework pour ça
- la gestion des erreurs souvent en dur, se rapprocher de notre framework pour ça

Ce qui est superbe :
- Interface Store, et différents moteurs de stockage abstraits, à reprendre en l'état
- Le code en général, parfaitement commenté, succinct, bien scindé, respecter ça
- Les différentes classes qui représentent parfaitement les choses, request, router… respecter ça
- Le rangement du $_POST, $_GET, $_SESSION, $_SERVER dans le Request, reprendre ça
- Les dossiers avec des majuscules
- Le profiler, ça déchire
- isAjax dans le Request
- la communication entre les objets, usage de static

Autres idées
- limiter les routes en fonction d'une méthode

Première phase :
- Repartir de rien et écrire le cheminement MINIMAL d'une requête en s'inspirant du meilleur des deux framework.

Seconde phase :
- enrichir en fonctionnalités avec le code repris depuis l'un ou l'autre des frameworks, en le nettoyant abondamment au besoin. (pfNotice, etc.)
- enrichir la classe pfFormat de formattage
- enrichir le dossier vendor dans un autre repo
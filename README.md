# bahn-api - Eine Implementation einer Infotafel für aktuelle An-/Abfahrten an einem Wunschbahnhof
Ziel dieser Webapp ist die Bereitstellung einer Infotafel beruhend auf den Daten aus den DB APIs Timetable, StationData und FacilitiesStatus

![image](https://github.com/Niklasthegeek/bahn-api/assets/77445745/9818b6c4-959c-4265-b5d1-dd6a0f2235a5)




# Vorraussetzungen

Webserver mit PHP + PHP-CURL

# Installation

1. Projekt klonen
  ```
  git clone https://github.com/Niklasthegeek/bahn-api/
  ```
2. In Verzeichnis navigieren
```
cd bahn-api
```
3. secrets.txt.example Datei umbenennen
```
mv secrets.txt.example secrets.txt
```
4. secrets.txt editieren und mit API Keys füllen
5. Inhalte aus dem Verzeichnis in das Webroot kopieren
```
cp ./* /var/www/html/
```
6. .htaccess Datei im Webroot erstellen, um die secret datei nicht öffentlich zu machen
```
RedirectMatch 404 \.txt$
<Files ~ "\.txt$">
    Order allow,deny
    Deny from all
</Files>
```
7. Um Caching zu aktivieren erstellen sie bitte den ordner cache im webroot mit schreibrechten für den www-data user oder chmod 777
```
mkdir cache
```



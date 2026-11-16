@startuml
title Диаграммаи ҷойгиркунии веблоиҳаи маркази таъмири техника

skinparam shadowing false
skinparam componentStyle rectangle
skinparam defaultTextAlignment center

node "Дастгоҳи истифодабаранда" as client {
  artifact "Веб-браузер" as browser
}

cloud "Интернет" as internet

node "Сервери веб" as webserver {
  artifact "Веб-барномаи маркази таъмири техника" as app
  artifact "PHP" as php
  artifact "HTML / CSS / JavaScript" as frontend
}

database "Сервери пойгоҳи додаҳо" as dbserver {
  artifact "MySQL" as mysql
  artifact "users, services, devices,\nrepair_orders, diagnoses,\norder_status_history, parts,\norder_parts, payments,\nnotifications, reviews" as tables
}

node "Анбори файлҳо" as storage {
  folder "uploads/" as uploads
  artifact "Аксҳои дастгоҳҳо ва\nҳуҷҷатҳои таъмир" as files
}

cloud "Хизматрасониҳои беруна" as external {
  artifact "Email / SMS\n(агар пайваст карда шавад)" as messaging
}

browser --> internet : HTTPS
internet --> webserver : HTTPS
webserver --> app
app --> php
app --> frontend
php --> mysql : SQL
mysql --> tables
app --> uploads : Сабт ва хондани файлҳо
uploads --> files
app --> messaging : SMTP / API

note right of app
  Амалҳои асосӣ:
  - Бақайдгирӣ ва воридшавӣ
  - Сабти дастгоҳ
  - Қабули фармоиши таъмир
  - Таъини усто
  - Ташхиси нуқсон
  - Идоракунии раванди таъмир
  - Сабти қисмҳои эҳтиётӣ
  - Ҳисоб ва сабти пардохт
  - Огоҳ кардани муштарӣ
end note

note bottom of dbserver
  Дастрасӣ ба MySQL танҳо аз тарафи
  сервери барнома иҷозат дода мешавад.
  Санҷиши шахсият ва ҳуқуқи дастрасӣ
  дар тарафи сервер иҷро мегардад.
end note

@enduml

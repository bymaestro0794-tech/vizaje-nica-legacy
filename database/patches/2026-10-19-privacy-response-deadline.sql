UPDATE menu
SET
    descRU = REPLACE(
        descRU,
        'и в сроки, предусмотренные применимым законодательством.',
        'и, как правило, не позднее одного месяца с даты получения запроса, если применимое законодательство не предусматривает иное.'
    ),
    descRO = REPLACE(
        descRO,
        'și în termenele prevăzute de legislația aplicabilă.',
        'și, de regulă, în cel mult o lună de la primirea solicitării, dacă legislația aplicabilă nu prevede altfel.'
    )
WHERE id = 23;

-- Некоторые DEV-дампы содержат русскую строку с нестандартными пробелами,
-- поэтому добавляем понятный срок отдельным абзацем, если замена выше не сработала.
UPDATE menu
SET descRU = CONCAT(
    descRU,
    '<p><br>Запросы обычно рассматриваются не позднее одного месяца с даты получения. Если требуется продление в случаях, предусмотренных законом, заявитель будет уведомлён отдельно.</p><!--privacy-response-deadline-ru-v1-->'
)
WHERE id = 23
  AND LOCATE('privacy-response-deadline-ru-v1', descRU) = 0;

UPDATE menu
SET descRO = CONCAT(
    descRO,
    '<p><br>Solicitările sunt, de regulă, soluționate în cel mult o lună de la primire. Dacă legea permite o prelungire, solicitantul va fi informat separat.</p><!--privacy-response-deadline-ro-v1-->'
)
WHERE id = 23
  AND LOCATE('privacy-response-deadline-ro-v1', descRO) = 0;

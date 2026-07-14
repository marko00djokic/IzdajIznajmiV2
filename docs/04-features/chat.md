# Chat i realtime

> Status: `implemented` sa polling fallbackom
> Poslednja ciljana provera: 2026-07-15
> Source of truth: Conversation/Chat kontroleri, events, frontend chat kod i testovi

Razgovor je listing/application-scoped i dostupan učesnicima odnosno adminu
prema autorizacionim pravilima. Poruke podržavaju privatne attachment-e,
read-state, typing i presence signale. Report tok ulazi u admin moderaciju.

Reverb emituje događaje preko Pusher protokola, ali UI i dalje koristi polling:
poruke imaju backoff 3–30 sekundi, typing približno 4 sekunde, presence refresh
30 sekundi i notification bell 15 sekundi dok je dokument vidljiv. Realtime
incident zato proverava i WebSocket i polling ponašanje.

| Sloj | Putanje |
| --- | --- |
| Frontend | `pages/Messages.vue`, `pages/Chat.vue`, `stores/chat.ts`, `services/echo.ts` |
| Backend | `ConversationController`, `ChatSignalController`, `ChatAttachmentController`, events/listeners |
| Podaci | conversations, messages, chat_attachments |
| Testovi | `ChatApiTest.php`, `ChatSignalsTest.php` |

Operativna dijagnostika je u [chat runbooku](../07-operations/chat-realtime.md).

# KYC verifikacija

> Status: `implemented`; AV skeniranje je env-zavisno
> Poslednja ciljana provera: 2026-07-15
> Source of truth: KYC config, Requests, kontroleri, policy, jobs i testovi

Autentifikovani `seeker` ili `landlord` može poslati KYC dokumente. Potrebni su
prednja strana ID-a, selfie i dokaz adrese; zadnja strana ID-a je opciona.
Dozvoljeni su JPEG/PNG/WebP/PDF do konfigurisanog limita (podrazumevano 10 MB),
uz validaciju ekstenzije/MIME-a i magic bytes sadržaja.

Dozvoljena je jedna `pending` prijava. Korisnik može povući sopstvenu pending
prijavu; admin sa odgovarajućim MFA/role gate-om pregleda, odobrava, odbija ili
redact-uje dokumente.

Dokumenti su na private disku i streamuju se samo kroz autorizovan endpoint.
Vlasnik i admin imaju pristup; pristup se auditira. Retention je konfigurisan
(`KYC_DOCUMENT_RETENTION_DAYS`, default 90), a scheduler pokreće purge. ClamAV
skeniranje se queue-uje samo kada je `ENABLE_AV_SCAN` uključeno.

Detaljna bezbednost je u
[KYC document security](security/kyc-document-security.md). Testovi su u
`backend/tests/Feature/KycApiTest.php` i security/UAT planovima.

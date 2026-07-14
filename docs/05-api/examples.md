# API Examples (cURL)

Uses Sanctum cookie/session auth.
Keep a cookie jar and send `X-XSRF-TOKEN` for state-changing requests.

```bash
# 1) Get CSRF cookie
curl -c cookies.txt -X GET http://localhost:8000/sanctum/csrf-cookie
XSRF=$(grep XSRF-TOKEN cookies.txt | tail -n1 | awk '{print $7}')

# 2) Login as seeker
curl -b cookies.txt -c cookies.txt -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: $XSRF" \
  -d '{"email":"trazilac1@gmail.com","password":"password"}'

# 3) Public listings search
curl -b cookies.txt "http://localhost:8000/api/v1/listings?category=villa&priceMin=100&priceMax=300"

# 4) Apply to listing (application flow)
curl -b cookies.txt -c cookies.txt -X POST http://localhost:8000/api/v1/listings/1/apply \
  -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: $XSRF" \
  -d '{"message":"I am interested. Can we discuss terms?","startDate":"2030-03-10","endDate":"2030-04-10"}'

# Note: reservation window must be at least one month.

# 5) Seeker applications
curl -b cookies.txt http://localhost:8000/api/v1/seeker/applications

# 6) Request viewing slot (replace slot id)
curl -b cookies.txt -c cookies.txt -X POST http://localhost:8000/api/v1/viewing-slots/1/request \
  -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: $XSRF" \
  -d '{"message":"Weekend works best for me."}'

# 7) Notifications + unread count
curl -b cookies.txt http://localhost:8000/api/v1/notifications
curl -b cookies.txt http://localhost:8000/api/v1/notifications/unread-count

# 8) Logout seeker
curl -b cookies.txt -c cookies.txt -X POST http://localhost:8000/api/v1/auth/logout \
  -H "X-XSRF-TOKEN: $XSRF"
```

Landlord flow example (separate session):

```bash
# A) Landlord login
curl -c landlord-cookies.txt -X GET http://localhost:8000/sanctum/csrf-cookie
L_XSRF=$(grep XSRF-TOKEN landlord-cookies.txt | tail -n1 | awk '{print $7}')

curl -b landlord-cookies.txt -c landlord-cookies.txt -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: $L_XSRF" \
  -d '{"email":"stanodavac1@gmail.com","password":"password"}'

# B) Landlord applications inbox
curl -b landlord-cookies.txt http://localhost:8000/api/v1/landlord/applications

# C) Accept application (replace application id)
curl -b landlord-cookies.txt -c landlord-cookies.txt -X PATCH http://localhost:8000/api/v1/applications/1 \
  -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: $L_XSRF" \
  -d '{"status":"accepted"}'

# D) Confirm viewing request (replace request id)
curl -b landlord-cookies.txt -c landlord-cookies.txt -X PATCH http://localhost:8000/api/v1/viewing-requests/1/confirm \
  -H "X-XSRF-TOKEN: $L_XSRF"
```

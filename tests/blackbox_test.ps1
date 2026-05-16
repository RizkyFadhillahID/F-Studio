#!/usr/bin/env pwsh
# ============================================================
# F-Studio SmartHub — Full Blackbox API Test Suite
# ============================================================
$BASE = "http://127.0.0.1:8000/api/v1"
$PASS = 0; $FAIL = 0; $ERRORS = @()

function req {
    param($method, $path, $body=$null, $token=$null, [switch]$raw)
    $headers = @{ "Accept"="application/json"; "Content-Type"="application/json" }
    if ($token) { $headers["Authorization"] = "Bearer $token" }
    $params = @{ Method=$method; Uri="$BASE$path"; Headers=$headers; ErrorAction="SilentlyContinue" }
    if ($body) { $params["Body"] = ($body | ConvertTo-Json -Depth 5) }
    try {
        $resp = Invoke-RestMethod @params
        if ($raw) { return $resp }
        return $resp
    } catch {
        $errBody = $null
        try { $errBody = $_.ErrorDetails.Message | ConvertFrom-Json } catch {}
        if ($raw) { return $errBody }
        return $errBody
    }
}

function reqRaw {
    param($method, $path, $body=$null, $token=$null)
    $headers = @{ "Accept"="application/json"; "Content-Type"="application/json" }
    if ($token) { $headers["Authorization"] = "Bearer $token" }
    $params = @{ Method=$method; Uri="$BASE$path"; Headers=$headers; ErrorAction="SilentlyContinue" }
    if ($body) { $params["Body"] = ($body | ConvertTo-Json -Depth 5) }
    try {
        $resp = Invoke-WebRequest @params -UseBasicParsing
        return @{ status=$resp.StatusCode; body=($resp.Content | ConvertFrom-Json) }
    } catch {
        $status = $_.Exception.Response.StatusCode.value__
        $errBody = $null
        try { $errBody = $_.ErrorDetails.Message | ConvertFrom-Json } catch {}
        return @{ status=$status; body=$errBody }
    }
}

function check {
    param($label, $got, $expected)
    if ($got -eq $expected) {
        Write-Host "  [PASS] $label" -ForegroundColor Green
        $script:PASS++
    } else {
        Write-Host "  [FAIL] $label  (got=$got, expected=$expected)" -ForegroundColor Red
        $script:FAIL++
        $script:ERRORS += $label
    }
}

function section { param($name) Write-Host "`n=== $name ===" -ForegroundColor Cyan }

# ──────────────────────────────────────────────────────────────
# Reset DB to ensure idempotent test runs
Write-Host "`n[SETUP] Resetting database..." -ForegroundColor Yellow
$artisan = Join-Path (Split-Path $PSScriptRoot -Parent) "artisan"
php $artisan migrate:fresh --seed --force 2>&1 | Out-Null
php $artisan cache:clear 2>&1 | Out-Null
Write-Host "[SETUP] Database reset complete.`n" -ForegroundColor Yellow

# ──────────────────────────────────────────────────────────────
section "1. AUTHENTICATION"

# 1a. Login with wrong password (must be >=8 chars to pass validation, then hit 401 from credential check)
$r = reqRaw POST "/auth/login" @{ email="admin@fstudio.id"; password="wrongpassword123"; device_name="test" }
check "Login: invalid password → 401" $r.status 401

# 1b. Login with missing fields
$r = reqRaw POST "/auth/login" @{ email="admin@fstudio.id" }
check "Login: missing required fields → 422" $r.status 422

# 1c. Admin login OK
$r = reqRaw POST "/auth/login" @{ email="admin@fstudio.id"; password="password123"; device_name="test-device" }
check "Login: admin OK → 200" $r.status 200
$ADMIN_TOKEN = $r.body.data.token
check "Login: token present" ([bool]$ADMIN_TOKEN) $true

# 1d. Member login OK
$r = reqRaw POST "/auth/login" @{ email="member@fstudio.id"; password="password123"; device_name="test-device" }
check "Login: member OK → 200" $r.status 200
$MEMBER_TOKEN = $r.body.data.token

# 1e. Me endpoint
$r = reqRaw GET "/auth/me" -token $ADMIN_TOKEN
check "Me: admin → 200" $r.status 200
check "Me: role=admin" $r.body.data.role "admin"

# 1f. Unauthenticated access
$r = reqRaw GET "/categories"
check "Unauth: categories → 401" $r.status 401

# ──────────────────────────────────────────────────────────────
section "2. CATEGORIES (admin CRUD)"

# 2a. List
$r = reqRaw GET "/categories" -token $ADMIN_TOKEN
check "Categories: list → 200" $r.status 200
$CAT_ID = $r.body.data[0].id

# 2b. Show
$r = reqRaw GET "/categories/$CAT_ID" -token $ADMIN_TOKEN
check "Categories: show → 200" $r.status 200

# 2c. Create
$r = reqRaw POST "/categories" @{ name="Test Kategori Baru"; description="Deskripsi tes" } -token $ADMIN_TOKEN
check "Categories: create → 201" $r.status 201
$NEW_CAT_ID = $r.body.data.id
check "Categories: name saved" $r.body.data.name "Test Kategori Baru"

# 2d. Duplicate name → 422
$r = reqRaw POST "/categories" @{ name="Test Kategori Baru" } -token $ADMIN_TOKEN
check "Categories: duplicate name → 422" $r.status 422

# 2e. Update
$r = reqRaw PUT "/categories/$NEW_CAT_ID" @{ name="Test Kategori Updated" } -token $ADMIN_TOKEN
check "Categories: update → 200" $r.status 200
check "Categories: name updated" $r.body.data.name "Test Kategori Updated"

# 2f. Member cannot create category
$r = reqRaw POST "/categories" @{ name="Member Cat" } -token $MEMBER_TOKEN
check "Categories: member create → 403" $r.status 403

# 2g. Delete
$r = reqRaw DELETE "/categories/$NEW_CAT_ID" -token $ADMIN_TOKEN
check "Categories: delete → 200" $r.status 200

# ──────────────────────────────────────────────────────────────
section "3. EQUIPMENT (admin CRUD)"

# 3a. List
$r = reqRaw GET "/equipment" -token $ADMIN_TOKEN
check "Equipment: list → 200" $r.status 200
$EQUIP_ID = $r.body.data[0].id
$EQUIP_CODE = $r.body.data[0].code

# 3b. Show
$r = reqRaw GET "/equipment/$EQUIP_ID" -token $ADMIN_TOKEN
check "Equipment: show → 200" $r.status 200

# 3c. Filter by available
$r = reqRaw GET "/equipment?available=1" -token $MEMBER_TOKEN
check "Equipment: available filter → 200" $r.status 200

# 3d. Member cannot create equipment
$r = reqRaw POST "/equipment" @{ name="X"; code="X-001"; quantity_total=1; condition="good"; category_id=1 } -token $MEMBER_TOKEN
check "Equipment: member create → 403" $r.status 403

# 3e. Create (admin)
$catList = req GET "/categories" -token $ADMIN_TOKEN
$firstCatId = $catList.data[0].id
$r = reqRaw POST "/equipment" @{
    name="Test Equipment"; code="TST-999"; category_id=$firstCatId
    quantity_total=3; condition="good"; location="Rak Test"
} -token $ADMIN_TOKEN
check "Equipment: create → 201" $r.status 201
$NEW_EQUIP_ID = $r.body.data.id

# 3f. Duplicate code → 422
$r = reqRaw POST "/equipment" @{
    name="Test2"; code="TST-999"; category_id=$firstCatId
    quantity_total=1; condition="good"
} -token $ADMIN_TOKEN
check "Equipment: duplicate code → 422" $r.status 422

# 3g. Delete
$r = reqRaw DELETE "/equipment/$NEW_EQUIP_ID" -token $ADMIN_TOKEN
check "Equipment: delete → 200" $r.status 200

# ──────────────────────────────────────────────────────────────
section "4. ROOMS (admin CRUD)"

# 4a. List
$r = reqRaw GET "/rooms" -token $ADMIN_TOKEN
check "Rooms: list → 200" $r.status 200
$ROOM_ID = $r.body.data[0].id

# 4b. Show
$r = reqRaw GET "/rooms/$ROOM_ID" -token $ADMIN_TOKEN
check "Rooms: show → 200" $r.status 200

# 4c. Create
$r = reqRaw POST "/rooms" @{
    name="Ruang Test BB"; code="TST-01"; capacity=5
    facilities=@("WiFi","AC")
} -token $ADMIN_TOKEN
check "Rooms: create → 201" $r.status 201
$NEW_ROOM_ID = $r.body.data.id

# 4d. Duplicate code → 422
$r = reqRaw POST "/rooms" @{ name="X"; code="TST-01"; capacity=1 } -token $ADMIN_TOKEN
check "Rooms: duplicate code → 422" $r.status 422

# 4e. Member cannot create
$r = reqRaw POST "/rooms" @{ name="X"; code="MB-01"; capacity=1 } -token $MEMBER_TOKEN
check "Rooms: member create → 403" $r.status 403

# 4f. Delete
$r = reqRaw DELETE "/rooms/$NEW_ROOM_ID" -token $ADMIN_TOKEN
check "Rooms: delete → 200" $r.status 200

# ──────────────────────────────────────────────────────────────
section "5. BOOKINGS"

# 5a. Member creates booking
$startDt = (Get-Date).AddDays(1).ToString("yyyy-MM-dd") + " 10:00:00"
$endDt   = (Get-Date).AddDays(1).ToString("yyyy-MM-dd") + " 12:00:00"
$r = reqRaw POST "/bookings" @{
    room_id=$ROOM_ID; title="Sesi Foto Produk"
    start_datetime=$startDt; end_datetime=$endDt
    notes="Butuh lighting set"
} -token $MEMBER_TOKEN
check "Bookings: member create → 201" $r.status 201
$BOOKING_ID = $r.body.data.id
$BOOKING_CODE = $r.body.data.booking_code
check "Bookings: code format BK-" ($BOOKING_CODE -like "BK-*") $true

# 5b. Conflict booking same room/time
$r = reqRaw POST "/bookings" @{
    room_id=$ROOM_ID; title="Konflik"
    start_datetime=$startDt; end_datetime=$endDt
} -token $MEMBER_TOKEN
check "Bookings: conflict → 422" $r.status 422

# 5c. End before start → 422
$r = reqRaw POST "/bookings" @{
    room_id=$ROOM_ID; title="Invalid"
    start_datetime=$endDt; end_datetime=$startDt
} -token $MEMBER_TOKEN
check "Bookings: end_before_start → 422" $r.status 422

# 5d. Member can see own booking
$r = reqRaw GET "/bookings/$BOOKING_ID" -token $MEMBER_TOKEN
check "Bookings: owner can view → 200" $r.status 200

# 5e. Admin can approve
$r = reqRaw POST "/bookings/$BOOKING_ID/approve" @{ admin_notes="Disetujui" } -token $ADMIN_TOKEN
check "Bookings: admin approve → 200" $r.status 200
check "Bookings: status=approved" $r.body.data.status "approved"

# 5f. Cannot approve again
$r = reqRaw POST "/bookings/$BOOKING_ID/approve" @{} -token $ADMIN_TOKEN
check "Bookings: double-approve → 422" $r.status 422

# 5g. Admin creates another booking to reject
$start2 = (Get-Date).AddDays(2).ToString("yyyy-MM-dd") + " 09:00:00"
$end2   = (Get-Date).AddDays(2).ToString("yyyy-MM-dd") + " 11:00:00"
$r = reqRaw POST "/bookings" @{
    room_id=$ROOM_ID; title="Booking untuk Ditolak"
    start_datetime=$start2; end_datetime=$end2
} -token $MEMBER_TOKEN
$BOOKING_REJECT_ID = $r.body.data.id
$r = reqRaw POST "/bookings/$BOOKING_REJECT_ID/reject" @{ admin_notes="Ruangan maintenance" } -token $ADMIN_TOKEN
check "Bookings: admin reject → 200" $r.status 200
check "Bookings: status=rejected" $r.body.data.status "rejected"

# ──────────────────────────────────────────────────────────────
section "6. EQUIPMENT LOANS"

# Get equipment IDs for loan
$eqList = req GET "/equipment" -token $MEMBER_TOKEN
$eq1 = $eqList.data[0]
$eq2 = $eqList.data[1]

$dueDt = (Get-Date).AddDays(7).ToString("yyyy-MM-dd")
$r = reqRaw POST "/equipment-loans" @{
    purpose="Keperluan dokumentasi event"
    due_date=$dueDt
    items=@(
        @{ equipment_id=$eq1.id; quantity=1; notes="Perlu case pelindung" }
        @{ equipment_id=$eq2.id; quantity=1 }
    )
} -token $MEMBER_TOKEN
check "Loans: member create → 201" $r.status 201
$LOAN_ID = $r.body.data.id
$LOAN_CODE = $r.body.data.loan_code
check "Loans: code format LN-" ($LOAN_CODE -like "LN-*") $true

# 6b. Exceed available quantity
$r = reqRaw POST "/equipment-loans" @{
    purpose="Test over quantity"
    due_date=$dueDt
    items=@(@{ equipment_id=$eq1.id; quantity=999 })
} -token $MEMBER_TOKEN
check "Loans: exceed quantity → 422" $r.status 422

# 6c. Admin approve
$r = reqRaw POST "/equipment-loans/$LOAN_ID/approve" @{ admin_notes="Silakan diambil" } -token $ADMIN_TOKEN
check "Loans: admin approve → 200" $r.status 200
check "Loans: status=approved" $r.body.data.status "approved"

# 6d. Member cannot approve
$r = reqRaw POST "/equipment-loans/$LOAN_ID/approve" @{} -token $MEMBER_TOKEN
check "Loans: member approve → 403" $r.status 403

# ──────────────────────────────────────────────────────────────
section "7. CHECK-IN / CHECK-OUT"

# 7a. Check-in
$r = reqRaw POST "/check-ins" @{
    loan_code=$LOAN_CODE
    action="check_in"
    device_id="TABLET-LOBBY-01"
    latitude=-6.2088
    longitude=106.8456
    notes="Peralatan diambil"
} -token $MEMBER_TOKEN
check "CheckIn: check_in → 201" $r.status 201
check "CheckIn: loan status=active" $r.body.data.loan.status "active"

# 7b. Check-out — build item_conditions as array of {item_id, condition} objects
$itemConditionsArr = @()
foreach ($item in $r.body.data.loan.items) {
    $itemConditionsArr += @{ item_id=$item.id; condition="good" }
}
$r = reqRaw POST "/check-ins" @{
    loan_code=$LOAN_CODE
    action="check_out"
    device_id="TABLET-LOBBY-01"
    latitude=-6.2088
    longitude=106.8456
    notes="Dikembalikan kondisi baik"
    item_conditions=$itemConditionsArr
} -token $MEMBER_TOKEN
check "CheckIn: check_out → 200" $r.status 200
check "CheckIn: loan status=returned" $r.body.data.loan.status "returned"

# 7c. Admin can list check-ins
$r = reqRaw GET "/check-ins" -token $ADMIN_TOKEN
check "CheckIns: admin list → 200" $r.status 200

# 7d. Member cannot list check-ins
$r = reqRaw GET "/check-ins" -token $MEMBER_TOKEN
check "CheckIns: member list → 403" $r.status 403

# ──────────────────────────────────────────────────────────────
section "8. NOTIFICATIONS"

$r = reqRaw GET "/notifications" -token $MEMBER_TOKEN
check "Notifications: list → 200" $r.status 200
$notifCount = $r.body.meta.total
Write-Host "  [INFO] Notifikasi member: $notifCount" -ForegroundColor Yellow

# Mark single notification as read
if ($r.body.data -and $r.body.data.Count -gt 0) {
    $notifId = $r.body.data[0].id
    $r2 = reqRaw POST "/notifications/$notifId/read" -token $MEMBER_TOKEN
    check "Notifications: markOneRead → 200" $r2.status 200
}

# Mark all as read
$r = reqRaw POST "/notifications/read-all" -token $MEMBER_TOKEN
check "Notifications: markAllRead → 200" $r.status 200

# ──────────────────────────────────────────────────────────────
# ──────────────────────────────────────────────────────────────
section "8b. MISSING ENDPOINT COVERAGE"

# Booking: update (still pending — create a fresh one)
$start3 = (Get-Date).AddDays(5).ToString("yyyy-MM-dd") + " 14:00:00"
$end3   = (Get-Date).AddDays(5).ToString("yyyy-MM-dd") + " 16:00:00"
$r = reqRaw POST "/bookings" @{
    room_id=$ROOM_ID; title="Booking Update Test"
    start_datetime=$start3; end_datetime=$end3
} -token $MEMBER_TOKEN
$BOOKING_UPDATE_ID = $r.body.data.id
$r = reqRaw PUT "/bookings/$BOOKING_UPDATE_ID" @{ title="Judul Diperbarui"; notes="Update test" } -token $MEMBER_TOKEN
check "Bookings: update own pending → 200" $r.status 200
check "Bookings: title updated" $r.body.data.title "Judul Diperbarui"

# Booking: delete (cancel) own pending
$r = reqRaw DELETE "/bookings/$BOOKING_UPDATE_ID" -token $MEMBER_TOKEN
check "Bookings: cancel own pending → 200" $r.status 200

# Equipment Loan: show by ID
$r = reqRaw GET "/equipment-loans/$LOAN_ID" -token $MEMBER_TOKEN
check "Loans: show by ID → 200" $r.status 200

# Equipment Loan: update (need pending loan)
$dueDt2 = (Get-Date).AddDays(10).ToString("yyyy-MM-dd")
$eqList2 = req GET "/equipment" -token $MEMBER_TOKEN
$eq3 = $eqList2.data[0]
$r = reqRaw POST "/equipment-loans" @{
    purpose="Loan for update test"; due_date=$dueDt2
    items=@(@{ equipment_id=$eq3.id; quantity=1 })
} -token $MEMBER_TOKEN
$LOAN_UPDATE_ID = $r.body.data.id
$r = reqRaw PUT "/equipment-loans/$LOAN_UPDATE_ID" @{ purpose="Updated purpose"; due_date=$dueDt2 } -token $MEMBER_TOKEN
check "Loans: update own pending → 200" $r.status 200

# Check-in: admin show by ID
$ciList = req GET "/check-ins" -token $ADMIN_TOKEN
if ($ciList.data -and $ciList.data.Count -gt 0) {
    $ciId = $ciList.data[0].id
    $r = reqRaw GET "/check-ins/$ciId" -token $ADMIN_TOKEN
    check "CheckIns: admin show by ID → 200" $r.status 200
}

# ──────────────────────────────────────────────────────────────
section "9. RBAC & SECURITY"

# 9a. Unauthenticated → 401
$r = reqRaw GET "/bookings"
check "RBAC: no token → 401" $r.status 401

# 9b. Invalid token → 401
$r = reqRaw GET "/bookings" -token "fake-invalid-token-12345"
check "RBAC: bad token → 401" $r.status 401

# 9c. Member cannot access admin-only endpoints
$r = reqRaw DELETE "/categories/1" -token $MEMBER_TOKEN
check "RBAC: member delete category → 403" $r.status 403

$r = reqRaw GET "/check-ins" -token $MEMBER_TOKEN
check "RBAC: member list check-ins → 403" $r.status 403

# 9d. Logout invalidates token
$r = reqRaw POST "/auth/logout" -token $ADMIN_TOKEN
check "Auth: logout → 200" $r.status 200
$r = reqRaw GET "/auth/me" -token $ADMIN_TOKEN
check "Auth: token invalidated after logout → 401" $r.status 401

# ──────────────────────────────────────────────────────────────
section "10. WEB ROUTES"

function web { param($path)
    try {
        $r = Invoke-WebRequest -Uri "http://127.0.0.1:8000$path" -MaximumRedirection 0 -UseBasicParsing -ErrorAction SilentlyContinue
        return $r.StatusCode
    } catch {
        return $_.Exception.Response.StatusCode.value__
    }
}
$code = web "/login"
check "Web: GET /login → 200" $code 200
$code = web "/"
check "Web: GET / unauthenticated → 200 (landing page)" $code 200
$code = web "/dashboard"
check "Web: GET /dashboard unauthenticated → redirect (302)" $code 302

# ──────────────────────────────────────────────────────────────
section "RESULTS"
$TOTAL = $PASS + $FAIL
Write-Host "`n  Total : $TOTAL" -ForegroundColor White
Write-Host "  PASS  : $PASS" -ForegroundColor Green
Write-Host "  FAIL  : $FAIL" -ForegroundColor $(if($FAIL -gt 0){"Red"}else{"Green"})
if ($ERRORS.Count -gt 0) {
    Write-Host "`n  Failed tests:" -ForegroundColor Red
    $ERRORS | ForEach-Object { Write-Host "    - $_" -ForegroundColor Red }
}

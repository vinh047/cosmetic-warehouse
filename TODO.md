# TODO: Fix Notifications theo yêu cầu

## ✅ Đã hiểu task và file structure

- [x] Search files về low stock logic và mail sending
- [x] Read relevant files (Listeners, Services, Notifications)
- [x] Xác định issues: `->to($notifiable->email)` thừa trong toMail()

## ⏳ Chờ approve plan

- [ ] User confirm plan

## 🔧 Implement changes

1. Sửa `app/Notifications/InventoryAlertNotification.php`: Xóa `->to($notifiable->email)`
2. Sửa `app/Notifications/DailyRevenueNotification.php`: Xóa `->to($notifiable->email)`
3. Test notification sending

## ✅ Test & Complete

- [ ] Chạy test tạo order để trigger low stock notification
- [ ] Verify email/database notifications work correctly

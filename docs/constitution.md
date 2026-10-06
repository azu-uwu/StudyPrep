# Project Constitution

## 1. Công nghệ

- Backend: **Laravel**.
- Frontend: **Blade + HTML/CSS/JavaScript**.
- Database: **MySQL**.
- Quản lý mã nguồn: **Git + GitHub**.
- Không tự ý thay đổi công nghệ đã thống nhất nếu chưa được nhóm thống nhất.

## 2. Kiến trúc

- Hệ thống được tổ chức theo mô hình **Client – Server**.
- Backend chịu trách nhiệm xử lý nghiệp vụ, xác thực, phân quyền và làm việc với cơ sở dữ liệu.
- Frontend chịu trách nhiệm hiển thị giao diện và tương tác với người dùng.
- Các thành phần trong hệ thống phải tuân theo kiến trúc đã được thống nhất.

## 3. Quy tắc phát triển

- Mọi thay đổi mã nguồn phải được quản lý bằng **Git**.
- Chức năng được phát triển theo các nhiệm vụ đã được phân công.
- Code phải tuân theo kiến trúc và tài liệu đặc tả đã thống nhất.
- Không tự ý thay đổi kiến trúc hoặc cách tổ chức hệ thống nếu chưa được thống nhất.
- Không tự ý thêm chức năng ngoài phạm vi đã được thống nhất của dự án.
- Ưu tiên hoàn thiện các chức năng chính trước khi phát triển các chức năng mở rộng.

## 4. Quy tắc cơ sở dữ liệu

- Sử dụng **MySQL**.
- Các bảng phải có khóa chính phù hợp.
- Các mối quan hệ giữa các bảng phải được thể hiện bằng **khóa ngoại (Foreign Key)** khi cần thiết.
- Đảm bảo tính toàn vẹn và nhất quán của dữ liệu.
- Không tự ý thay đổi cấu trúc cơ sở dữ liệu nếu chưa được thống nhất.
- Tên bảng, tên cột và kiểu dữ liệu phải nhất quán trong toàn bộ hệ thống.

## 5. Quy tắc cấu trúc đề thi

- Hệ thống phải tuân thủ **cấu trúc đề thi TOEIC đã được thống nhất**.
- Không tự ý thêm, bỏ hoặc thay đổi các Part trong đề thi.
- Số lượng câu hỏi và cấu trúc của từng phần phải phù hợp với cấu trúc đề thi đã xác định.
- Chức năng luyện tập và thi thử phải sử dụng đúng cấu trúc đã được thống nhất.
- Việc lấy câu hỏi ngẫu nhiên phải đảm bảo đáp ứng đúng cấu trúc của từng phần thi.

## 6. Bảo mật

- Mật khẩu người dùng không được lưu dưới dạng văn bản thuần.
- Thông tin nhạy cảm và thông tin cấu hình phải được lưu trong biến môi trường khi phù hợp.
- Không đưa mật khẩu, API key hoặc thông tin bí mật vào mã nguồn và repository.
- Các chức năng quản trị phải có cơ chế xác thực và phân quyền phù hợp.

## 7. Kiểm thử

- Các chức năng phải được kiểm thử trước khi xác nhận hoàn thành.
- Việc sửa lỗi không được làm ảnh hưởng đến các chức năng đã hoạt động.
- Sau khi sửa lỗi cần kiểm tra lại chức năng liên quan.

## 8. Tài liệu

- Các tài liệu `requirements.md`, `spec.md`, `architecture.md`, `database.md`, `plan.md` và `tasks.md` phải thống nhất với nhau.
- Khi có thay đổi lớn về yêu cầu, kiến trúc hoặc cơ sở dữ liệu, tài liệu liên quan phải được cập nhật.
- Code phải được phát triển dựa trên các yêu cầu và thiết kế đã được thống nhất.
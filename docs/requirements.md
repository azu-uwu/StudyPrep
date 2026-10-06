# System Requirements

## 1. Tổng quan

### 1.1. Mục đích

Hệ thống được xây dựng nhằm hỗ trợ học viên ôn luyện và thi thử các kỹ năng tiếng Anh theo cấu trúc bài thi được quy định trong `exam-structure.md`.

Hệ thống cung cấp các chức năng:
- Quản lý tài khoản người dùng.
- Quản lý nội dung và dữ liệu dành cho quản trị viên.
- Luyện tập các kỹ năng Listening, Reading, Speaking và Writing.
- Thi thử và kiểm tra đầu vào.
- Kiểm soát quá trình làm bài thi.
- Quản lý lượt thi của người dùng.
- Theo dõi lịch sử, thống kê và phân tích kết quả học tập.

### 1.2. Đối tượng sử dụng

Hệ thống có hai nhóm người dùng chính:

#### Học viên / Thí sinh

Học viên sử dụng hệ thống để:
- Quản lý tài khoản và thông tin cá nhân.
- Thiết lập mục tiêu học tập.
- Học từ vựng.
- Luyện tập các kỹ năng.
- Làm bài thi thử.
- Xem kết quả và lịch sử làm bài.
- Theo dõi tiến độ và điểm yếu.

#### Quản trị viên

Quản trị viên sử dụng hệ thống để:
- Quản lý tài khoản người dùng.
- Quản lý từ vựng.
- Quản lý ngân hàng câu hỏi.
- Quản lý đề thi.
- Tìm kiếm và lọc dữ liệu.

---

# 2. Quản lý người dùng

## 2.1. Đăng ký tài khoản

Hệ thống phải cho phép người dùng đăng ký tài khoản bằng thông tin cần thiết.

Hệ thống phải:
- Kiểm tra tính hợp lệ của thông tin đăng ký.
- Kiểm tra tài khoản/email đã tồn tại.
- Mã hóa mật khẩu trước khi lưu.
- Tạo tài khoản sau khi thông tin hợp lệ.
- Khởi tạo số lượt thi của người dùng theo quy định của hệ thống.

## 2.2. Đăng nhập

Hệ thống phải cho phép người dùng đăng nhập bằng tài khoản đã đăng ký.

Hệ thống phải:
- Kiểm tra thông tin đăng nhập.
- Xác thực mật khẩu.
- Kiểm tra trạng thái tài khoản.
- Từ chối đăng nhập nếu thông tin không hợp lệ hoặc tài khoản bị khóa.

## 2.3. Đăng nhập bằng Google

Hệ thống phải hỗ trợ người dùng đăng nhập bằng tài khoản Google.

Hệ thống phải xác định tài khoản người dùng tương ứng và tạo hoặc liên kết tài khoản khi phù hợp.

## 2.4. Quản lý hồ sơ

Học viên có thể xem và cập nhật thông tin cá nhân.

Thông tin có thể bao gồm:
- Họ tên.
- Thông tin tài khoản.
- Chứng chỉ hoặc mục tiêu thi.
- Các thông tin cá nhân được hệ thống hỗ trợ.

## 2.5. Quên mật khẩu

Hệ thống phải cung cấp chức năng hỗ trợ người dùng lấy lại quyền truy cập khi quên mật khẩu.

## 2.6. Đổi mật khẩu

Người dùng có thể thay đổi mật khẩu tài khoản.

Hệ thống phải kiểm tra thông tin cần thiết trước khi thực hiện thay đổi.

## 2.7. Nhắc nhở học

Hệ thống cung cấp chức năng nhắc nhở học tập cho người dùng theo chức năng được hỗ trợ.

---

# 3. Quản lý Admin

## 3.1. Quản lý từ vựng

Quản trị viên có thể:
- Thêm từ vựng.
- Xem danh sách từ vựng.
- Cập nhật từ vựng.
- Xóa từ vựng.

Thông tin từ vựng có thể bao gồm:
- Từ.
- Nghĩa.
- Từ loại.
- Phát âm.
- Ví dụ tiếng Anh.
- Ví dụ tiếng Việt.

## 3.2. Thêm từ vựng hàng loạt

Hệ thống hỗ trợ quản trị viên nhập nhiều từ vựng cùng lúc theo định dạng dữ liệu được hệ thống quy định.

Hệ thống phải kiểm tra dữ liệu trước khi lưu.

## 3.3. Quản lý câu hỏi

Quản trị viên có thể:
- Thêm câu hỏi.
- Xem câu hỏi.
- Cập nhật câu hỏi.
- Xóa câu hỏi.

Câu hỏi phải được gắn với đúng kỹ năng và Part tương ứng theo `exam-structure.md`.

## 3.4. Quản lý đề thi

Quản trị viên có thể quản lý các cấu trúc và đề thi được sử dụng trong hệ thống.

Đề thi phải tuân thủ cấu trúc đã được quy định.

## 3.5. Thêm câu hỏi hàng loạt

Hệ thống hỗ trợ quản trị viên nhập nhiều câu hỏi cùng lúc theo định dạng dữ liệu được hệ thống quy định.

Dữ liệu phải được kiểm tra trước khi đưa vào ngân hàng câu hỏi.

## 3.6. Quản lý tài khoản

Quản trị viên có thể:
- Xem danh sách người dùng.
- Tìm kiếm người dùng.
- Lọc người dùng.
- Khóa/chặn tài khoản.
- Xóa tài khoản.

## 3.7. Tìm kiếm và lọc

Hệ thống phải hỗ trợ quản trị viên tìm kiếm và lọc dữ liệu trong các khu vực quản lý phù hợp.

---

# 4. Luyện thi

## 4.1. Học từ vựng

Học viên có thể:
- Xem danh sách từ vựng.
- Lựa chọn chủ đề.
- Xem các từ thuộc chủ đề.
- Học từ vựng thông qua flashcard.

Hệ thống phải lưu trạng thái học từ vựng của người dùng.

### 4.1.1. Hồi lượt thi khi học từ vựng

Khi người dùng hoàn thành hoạt động học từ vựng bằng flashcard, hệ thống có thể hồi **1 lượt thi** cho người dùng theo quy định của hệ thống.

Quy tắc:
- Mỗi lần hoàn thành điều kiện hồi lượt được xác định, người dùng được cộng **1 lượt**.
- Số lượt không được vượt quá **5**.
- Nếu người dùng đã có 5 lượt, việc học từ vựng không làm số lượt vượt quá 5.
- Việc hồi lượt phải được kiểm soát để tránh cộng nhiều lượt cho cùng một lần học.

Các trò chơi hoặc hình thức học từ vựng mở rộng chỉ được triển khai khi nằm trong phạm vi được thống nhất của dự án.

---

## 4.2. Luyện Listening

Hệ thống phải cho phép học viên luyện Listening theo cấu trúc:

- Part 1 – Mô tả tranh: 6 câu.
- Part 2 – Hỏi và đáp: 25 câu.
- Part 3 – Hội thoại: 39 câu.
- Part 4 – Bài nói ngắn: 30 câu.

Học viên có thể lựa chọn Part phù hợp để luyện tập.

Đối với các nhóm câu hỏi sử dụng chung một đoạn hội thoại hoặc bài nói, hệ thống phải giữ nguyên mối quan hệ giữa nội dung và các câu hỏi liên quan.

---

## 4.3. Luyện Reading

Hệ thống phải cho phép học viên luyện Reading theo cấu trúc:

- Part 5 – Hoàn thành câu: 30 câu.
- Part 6 – Hoàn thành đoạn văn: 16 câu.
- Part 7 – Đọc hiểu: 54 câu.

Học viên có thể lựa chọn Part phù hợp để luyện tập.

Đối với các câu hỏi sử dụng chung một đoạn văn hoặc nội dung đọc, hệ thống phải giữ nguyên mối quan hệ giữa nội dung và các câu hỏi liên quan.

---

## 4.4. Luyện Speaking

Hệ thống phải cho phép học viên luyện Speaking theo 5 Part:

- Part 1 – Read a text aloud: câu 1–2.
- Part 2 – Describe a picture: câu 3–4.
- Part 3 – Respond to questions: câu 5–7.
- Part 4 – Respond to questions using information provided: câu 8–10.
- Part 5 – Express an opinion: câu 11.

Câu trả lời Speaking được thực hiện dưới dạng âm thanh.

Hệ thống phải lưu câu trả lời để phục vụ chấm điểm và phân tích kết quả.

---

## 4.5. Luyện Writing

Hệ thống phải cho phép học viên luyện Writing theo 3 Part:

- Part 1 – Write a sentence based on a picture: câu 1–5.
- Part 2 – Respond to a written request: câu 6–7.
- Part 3 – Write an opinion essay: câu 8.

Câu trả lời Writing được thực hiện dưới dạng văn bản.

Hệ thống phải lưu câu trả lời để phục vụ chấm điểm và phân tích kết quả.

---

# 5. Thi thử

## 5.1. Quản lý lượt thi

Mỗi người dùng được cấp tối đa **5 lượt thi**.

Hệ thống phải:
- Hiển thị số lượt thi hiện tại của người dùng.
- Không cho phép người dùng bắt đầu bài thi khi số lượt bằng 0.
- Trừ **1 lượt** khi người dùng bắt đầu một bài thi thử hợp lệ.
- Không cho phép số lượt thi nhỏ hơn 0.
- Số lượt tối đa của người dùng là **5**.

### 5.1.1. Trừ lượt khi làm bài

Khi người dùng xác nhận bắt đầu bài thi thử và bài thi được tạo thành công, hệ thống trừ 1 lượt.

Việc trừ lượt phải được thực hiện một cách nhất quán để tránh:
- Trừ nhiều lần cho cùng một bài thi.
- Không trừ lượt khi bài thi đã được bắt đầu.
- Tạo bài thi nhưng không cập nhật lượt.

### 5.1.2. Thoát khỏi bài thi

Việc người dùng thoát khỏi giao diện bài thi, chuyển sang cửa sổ/tab khác hoặc vi phạm quy định làm bài **không tự động hoàn lại lượt thi**.

Lượt đã sử dụng vẫn được tính theo quy định của hệ thống.

---

## 5.2. Tạo bài thi

Hệ thống phải cho phép tạo bài thi dựa trên cấu trúc đề đã được xác định.

Bài thi có thể được tạo cho:
- Toàn bộ bài thi.
- Một kỹ năng.

Việc lựa chọn câu hỏi phải đảm bảo:
- Đúng kỹ năng.
- Đúng Part.
- Đúng số lượng câu.
- Đúng thứ tự.
- Tuân thủ quy tắc của cấu trúc đề.

## 5.3. Thi thử toàn bài

Học viên có thể thực hiện bài thi thử toàn bộ theo cấu trúc được quy định.

Đề thi phải bao gồm các phần tương ứng với cấu trúc của bài thi.

## 5.4. Thi thử từng kỹ năng

Học viên có thể lựa chọn thực hiện bài thi thử theo từng kỹ năng được hệ thống hỗ trợ.

## 5.5. Kiểm tra đầu vào

Hệ thống cung cấp bài kiểm tra đầu vào nhằm đánh giá trình độ ban đầu của học viên.

Kết quả kiểm tra được sử dụng để phân loại hoặc xác định mức độ phù hợp của người học.

## 5.6. Kiểm soát giao diện khi làm bài

Trong quá trình thực hiện bài thi, hệ thống phải cung cấp cơ chế hạn chế các hành vi làm ảnh hưởng đến tính nghiêm túc của bài thi.

### 5.6.1. Chế độ toàn màn hình

Khi bắt đầu bài thi, hệ thống yêu cầu người dùng chuyển sang **chế độ toàn màn hình**.

Hệ thống phải:
- Hiển thị hướng dẫn trước khi bắt đầu.
- Yêu cầu người dùng xác nhận để vào chế độ làm bài.
- Theo dõi trạng thái toàn màn hình trong quá trình làm bài.

### 5.6.2. Phát hiện thoát toàn màn hình

Nếu người dùng thoát khỏi chế độ toàn màn hình trong khi đang làm bài, hệ thống phải:
- Phát hiện sự kiện.
- Hiển thị cảnh báo cho người dùng.
- Ghi nhận sự kiện vi phạm nếu hệ thống có lưu lịch sử sự kiện.

Hệ thống không tự động hoàn lại lượt thi khi người dùng thoát toàn màn hình.

### 5.6.3. Chuyển khỏi trang hoặc cửa sổ làm bài

Hệ thống phải phát hiện các trường hợp người dùng rời khỏi trang làm bài hoặc chuyển sang ứng dụng/tab khác trong phạm vi trình duyệt cho phép.

Khi phát hiện, hệ thống hiển thị cảnh báo cho người dùng.

### 5.6.4. Chặn sao chép

Trong giao diện làm bài, hệ thống phải hạn chế thao tác:
- Copy nội dung câu hỏi.
- Copy nội dung bài thi.
- Các thao tác sao chép trực tiếp bằng chuột hoặc bàn phím trong phạm vi có thể kiểm soát bằng trình duyệt.

Việc hạn chế này nhằm giảm khả năng sao chép nội dung trong quá trình làm bài.

### 5.6.5. Ghi nhận sự kiện

Các sự kiện liên quan đến việc rời khỏi chế độ làm bài có thể được ghi nhận để phục vụ:
- Cảnh báo người dùng.
- Theo dõi quá trình làm bài.
- Kiểm tra lịch sử vi phạm khi cần thiết.

---

## 5.7. Làm bài thi

Trong quá trình làm bài, hệ thống phải:
- Hiển thị câu hỏi theo đúng thứ tự.
- Cho phép người dùng trả lời theo loại câu hỏi.
- Lưu câu trả lời.
- Quản lý trạng thái bài làm.
- Cho phép nộp bài.
- Áp dụng các cơ chế kiểm soát làm bài được quy định tại mục 5.6.

## 5.8. Chấm điểm

Sau khi học viên hoàn thành bài thi, hệ thống phải thực hiện chấm điểm đối với các câu hỏi có thể chấm tự động.

Đối với Speaking và Writing, câu trả lời có thể được xử lý theo cơ chế chấm điểm được hệ thống hỗ trợ.

Kết quả chấm điểm phải được lưu để phục vụ xem kết quả và thống kê.

---

# 6. Thống kê và lịch sử bài làm

## 6.1. Lịch sử bài làm

Học viên có thể xem lịch sử:
- Bài luyện tập đã hoàn thành.
- Bài thi thử đã thực hiện.
- Kết quả tương ứng.

## 6.2. Thống kê kết quả

Hệ thống phải cung cấp thông tin thống kê về kết quả học tập và làm bài của học viên.

Các kết quả có thể được thống kê theo:
- Kỹ năng.
- Part.
- Bài làm.
- Thời gian.

## 6.3. Phân tích bài làm

Hệ thống phải cho phép học viên xem và phân tích kết quả của bài làm đã hoàn thành.

Thông tin phân tích có thể bao gồm:
- Điểm số.
- Kết quả theo kỹ năng.
- Kết quả theo Part.
- Câu trả lời đúng/sai.
- Các điểm yếu được xác định từ kết quả.

## 6.4. Gợi ý cải thiện

Dựa trên kết quả học tập và làm bài, hệ thống cung cấp các gợi ý giúp học viên xác định nội dung cần cải thiện.

## 6.5. So sánh kết quả

Hệ thống hỗ trợ so sánh kết quả của người học giữa các lần làm bài nhằm theo dõi sự thay đổi trong quá trình học tập.

---

# 7. Quy tắc chung về yêu cầu

## 7.1. Tuân thủ cấu trúc đề thi

Các chức năng liên quan đến luyện tập, thi thử, tạo đề và ngân hàng câu hỏi phải tuân thủ cấu trúc được quy định trong `exam-structure.md`.

## 7.2. Phân loại câu hỏi

Mỗi câu hỏi phải được xác định rõ:
- Kỹ năng.
- Part.
- Dạng câu hỏi phù hợp.
- Hình thức trả lời phù hợp.

## 7.3. Ngẫu nhiên câu hỏi

Khi hệ thống lấy câu hỏi ngẫu nhiên để tạo bài luyện hoặc đề thi, việc ngẫu nhiên phải nằm trong phạm vi câu hỏi phù hợp với cấu trúc đã xác định.

Đối với nhóm câu hỏi sử dụng chung một nội dung, hệ thống phải đảm bảo không làm mất ngữ cảnh của nhóm.

## 7.4. Phân quyền

Các chức năng quản trị chỉ được phép thực hiện bởi tài khoản có quyền quản trị.

Các chức năng cá nhân của học viên phải được giới hạn trong phạm vi tài khoản tương ứng.

## 7.5. Dữ liệu bài làm

Các câu trả lời và kết quả của người học phải được lưu trữ để phục vụ:
- Xem lại lịch sử.
- Tính điểm.
- Thống kê.
- Phân tích kết quả.
- Gợi ý cải thiện.

## 7.6. Quản lý lượt thi

Hệ thống phải đảm bảo:
- Mỗi người dùng có tối đa 5 lượt thi.
- Lượt thi được trừ khi bắt đầu bài thi thử hợp lệ.
- Lượt thi không được nhỏ hơn 0.
- Lượt thi được hồi thông qua hoạt động học từ vựng bằng flashcard theo quy định.
- Lượt thi không được vượt quá 5.
- Không được cộng hoặc trừ lượt nhiều lần cho cùng một sự kiện.

---

# 8. Tài liệu tham chiếu

Các yêu cầu trong tài liệu này được sử dụng cùng với:

- `constitution.md` – Quy tắc và nguyên tắc của dự án.
- `exam-structure.md` – Cấu trúc các bài thi và các Part.
- `database.md` – Thiết kế cơ sở dữ liệu.
- `architecture.md` – Kiến trúc hệ thống.
- `spec.md` – Đặc tả chi tiết hành vi của các chức năng.

Các tài liệu trên phải thống nhất với nhau. Khi có thay đổi về yêu cầu, các tài liệu liên quan phải được xem xét và cập nhật.
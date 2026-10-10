# Feature Specification: Hệ thống luyện thi TOEIC (StudyPrep)

**Feature Branch**: `main`

**Created**: 2026-10-09

**Status**: Draft

**Input**: User description: "Xây dựng hệ thống hỗ trợ học viên ôn luyện và thi thử các kỹ năng tiếng Anh TOEIC (Listening, Reading, Speaking, Writing), bao gồm quản lý tài khoản người dùng, quản lý nội dung cho quản trị viên, luyện tập, thi thử, kiểm tra đầu vào, kiểm soát quá trình làm bài, quản lý lượt thi và theo dõi kết quả học tập."

> **Tài liệu tham chiếu**: Tài liệu này được xây dựng dựa trên `constitution.md`, `exam-structure.md` và `requirements.md`. Các tài liệu phải thống nhất với nhau. Khi có thay đổi lớn về yêu cầu hoặc cấu trúc đề thi, tài liệu liên quan phải được cập nhật trước khi triển khai.

## User Scenarios & Testing *(mandatory)*

> Các user story được sắp xếp theo thứ tự ưu tiên giá trị. Mỗi story là một phần chức năng độc lập, có thể phát triển, kiểm thử và bàn giao riêng (mỗi story khi hoàn thành vẫn tạo ra giá trị sử dụng được).

### User Story 1 - Quản lý tài khoản người dùng (Priority: P1)

Học viên đăng ký tài khoản, đăng nhập (bao gồm cả đăng nhập bằng Google), quản lý hồ sơ cá nhân và thực hiện các thao tác bảo mật tài khoản (quên mật khẩu, đổi mật khẩu). Tài khoản mới được khởi tạo kèm số lượt thi ban đầu theo quy định. Học viên có thể bật nhắc nhở học tập.

**Why this priority**: Không thể sử dụng các tính năng luyện tập hay thi thử mà chỉ có thể xem trang chủ nếu chưa có tài khoản. Đây là nền tảng cho mọi chức năng cá nhân hóa khác (lưu tiến độ, lịch sử làm bài, lượt thi).

**Independent Test**: Có thể kiểm thử độc lập bằng cách đăng ký tài khoản mới, đăng nhập, cập nhật hồ sơ và đổi mật khẩu, sau đó xác nhận tài khoản hoạt động và có đúng số lượt thi khởi tạo.

**Acceptance Scenarios**:

1. **Given** học viên chưa có tài khoản, **When** học viên đăng ký với thông tin hợp lệ và email chưa tồn tại, **Then** hệ thống kiểm tra tính hợp lệ, mã hóa mật khẩu, tạo tài khoản, khởi tạo số lượt thi theo quy định và cho phép đăng nhập.
2. **Given** học viên đăng ký với email đã tồn tại, **When** học viên gửi biểu mẫu đăng ký, **Then** hệ thống từ chối và thông báo tài khoản/email đã tồn tại.
3. **Given** học viên có tài khoản, **When** học viên đăng nhập bằng thông tin hợp lệ, **Then** hệ thống xác thực mật khẩu, kiểm tra trạng thái tài khoản và cho đăng nhập thành công.
4. **Given** tài khoản bị khóa, **When** học viên cố gắng đăng nhập, **Then** hệ thống từ chối đăng nhập và thông báo tài khoản bị khóa.
5. **Given** học viên đăng nhập bằng Google, **When** học viên thực hiện đăng nhập qua Google, **Then** hệ thống xác định tài khoản tương ứng và tạo hoặc liên kết tài khoản khi phù hợp, sau đó đăng nhập thành công.
6. **Given** học viên quên mật khẩu, **When** học viên thực hiện quy trình lấy lại quyền truy cập, **Then** hệ thống hỗ trợ học viên khôi phục quyền truy cập vào tài khoản.
7. **Given** học viên muốn thay đổi mật khẩu, **When** học viên cung cấp thông tin cần thiết hợp lệ, **Then** hệ thống cập nhật mật khẩu mới cho tài khoản.
8. **Given** học viên có tài khoản, **When** học viên xem và cập nhật thông tin cá nhân, **Then** hệ thống lưu các thay đổi trong phạm vi tài khoản của chính học viên đó.
9. **Given** học viên muốn được nhắc nhở học, **When** học viên sử dụng chức năng nhắc nhở học tập theo cách hệ thống hỗ trợ, **Then** hệ thống gửi nhắc nhở học tập cho học viên.

---

### User Story 2 - Quản lý nội dung và dữ liệu cho quản trị viên (Priority: P1)

Quản trị viên quản lý từ vựng, ngân hàng câu hỏi, đề thi và tài khoản người dùng; có thể nhập dữ liệu hàng loạt và tìm kiếm, lọc dữ liệu trong các khu vực quản lý.

**Why this priority**: Nội dung (từ vựng, câu hỏi, cấu trúc đề) là dữ liệu đầu vào để các chức năng luyện tập và thi thử hoạt động. Chưa có nội dung thì học viên không thể luyện tập.

 
**Acceptance Scenarios**:

1. **Given** quản trị viên có quyền quản trị, **When** quản trị viên thêm, xem, cập nhật hoặc xóa từ vựng, **Then** hệ thống lưu thay đổi và hiển thị đúng trong danh sách từ vựng.
2. **Given** quản trị viên nhập nhiều từ vựng cùng lúc, **When** quản trị viên gửi dữ liệu theo định dạng hệ thống quy định, **Then** hệ thống kiểm tra dữ liệu trước khi lưu và chỉ lưu các bản ghi hợp lệ.
3. **Given** quản trị viên quản lý câu hỏi, **When** quản trị viên thêm, xem, cập nhật hoặc xóa câu hỏi, **Then** hệ thống yêu cầu câu hỏi gắn đúng kỹ năng và Part theo cấu trúc đề thi đã quy định.
4. **Given** quản trị viên nhập nhiều câu hỏi cùng lúc, **When** quản trị viên gửi dữ liệu theo định dạng hệ thống quy định, **Then** hệ thống kiểm tra dữ liệu trước khi đưa vào ngân hàng câu hỏi.
5. **Given** quản trị viên quản lý đề thi, **When** quản trị viên thao tác với cấu trúc đề và đề thi, **Then** hệ thống chỉ cho phép các cấu trúc tuân thủ cấu trúc đề thi đã quy định.
6. **Given** quản trị viên quản lý tài khoản, **When** quản trị viên xem, tìm kiếm, lọc, khóa hoặc xóa tài khoản người dùng, **Then** hệ thống thực hiện đúng thao tác tương ứng trên tài khoản được chọn.
7. **Given** khu vực quản lý có dữ liệu, **When** quản trị viên tìm kiếm hoặc lọc dữ liệu, **Then** hệ thống trả về danh sách kết quả phù hợp.
8. **Given** người dùng không có quyền quản trị, **When** người dùng truy cập chức năng quản trị, **Then** hệ thống chặn truy cập vì chức năng quản trị chỉ dành cho tài khoản có quyền quản trị.

### User Story 3 - Học từ vựng và luyện tập theo kỹ năng (Priority: P1)

Học viên học từ vựng qua flashcard theo chủ đề và luyện tập các kỹ năng Listening, Reading, Speaking, Writing theo từng Part hoặc theo phạm vi do hệ thống cung cấp. Khi hoàn thành hoạt động học từ vựng bằng flashcard, hệ thống có thể hồi **1 lượt thi** theo quy định. Hệ thống phải lưu trạng thái học tập và câu trả lời của học viên.

**Why this priority**: Đây là hoạt động sử dụng thường xuyên nhất của học viên và trực tiếp tạo ra dữ liệu phục vụ theo dõi tiến độ, đồng thời là kênh hồi lượt thi giúp học viên tiếp tục thi thử.

**Independent Test**: Có thể kiểm thử độc lập bằng cách học viên chọn một Part, hoàn thành bài luyện tập, và xác nhận câu hỏi được lấy đúng Part, câu trả lời được lưu và hoạt động flashcard hồi lượt thi đúng quy định.

**Acceptance Scenarios**:

1. **Given** học viên chọn một Part để luyện tập, **When** hệ thống tạo bài luyện tập, **Then** câu hỏi được lấy đúng từ Part đó theo cấu trúc đề thi đã quy định.
2. **Given** học viên luyện tập nhiều Part, **When** hệ thống tạo bài luyện tập, **Then** hệ thống xác định rõ phạm vi câu hỏi trước khi lấy câu hỏi.
3. **Given** nhóm câu hỏi dùng chung một nội dung (hội thoại, bài nói, đoạn văn, tài liệu đọc), **When** hệ thống lấy câu hỏi, **Then** hệ thống giữ nguyên mối quan hệ giữa nội dung chung và các câu hỏi liên quan, không làm mất ngữ cảnh của nhóm.
4. **Given** học viên học từ vựng qua flashcard, **When** học viên hoàn thành hoạt động theo điều kiện hồi lượt được xác định, **Then** hệ thống cộng **1 lượt thi** và không để số lượt vượt quá 5.
5. **Given** học viên đã có 5 lượt thi, **When** học viên hoàn thành hoạt động học từ vựng, **Then** hệ thống không làm số lượt vượt quá 5.
6. **Given** học viên trả lời câu hỏi Speaking bằng âm thanh, **When** hệ thống nhận câu trả lời, **Then** hệ thống lưu câu trả lời để phục vụ chấm điểm và phân tích kết quả.
7. **Given** học viên trả lời câu hỏi Writing bằng văn bản, **When** hệ thống nhận câu trả lời, **Then** hệ thống lưu câu trả lời để phục vụ chấm điểm và phân tích kết quả.
8. **Given** học viên luyện tập Listening theo cấu trúc Listening, **When** hệ thống tạo bài luyện, **Then** số câu từng Part đúng theo quy định (Part 1: 6, Part 2: 25, Part 3: 39, Part 4: 30).
9. **Given** học viên luyện tập Reading theo cấu trúc Reading, **When** hệ thống tạo bài luyện, **Then** số câu từng Part đúng theo quy định (Part 5: 30, Part 6: 16, Part 7: 54).

---

### User Story 4 - Thi thử, kiểm tra đầu vào và kiểm soát làm bài (Priority: P1)

Học viên thực hiện bài thi thử (toàn bài hoặc theo từng kỹ năng) và bài kiểm tra đầu vào dựa trên cấu trúc đề đã xác định. Hệ thống quản lý lượt thi (tối đa 5, trừ khi bắt đầu bài thi hợp lệ, hồi qua hoạt động học từ vựng), kiểm soát quá trình làm bài (toàn màn hình, chống thoát trang, chống sao chép, ghi nhận sự kiện) và chấm điểm tự động cho các câu hỏi có thể chấm được.

**Why this priority**: Thi thử và kiểm tra đầu vào là chức năng cốt lõi giúp học viên đánh giá trình độ và làm quen với đề thi thật; đồng thời chứa các quy tắc nghiệp vụ phức tạp nhất (lượt thi, kiểm soát giao diện, tạo đề).

**Independent Test**: Có thể kiểm thử độc lập bằng cách học viên bắt đầu một bài thi thử, xác nhận lượt thi bị trừ đúng, trả lời và nộp bài, sau đó xác nhận kết quả được lưu và điểm được tính.

**Acceptance Scenarios**:

1. **Given** học viên có số lượt thi lớn hơn 0, **When** học viên xác nhận bắt đầu bài thi thử và bài thi được tạo thành công, **Then** hệ thống trừ **1 lượt thi** và không trừ nhiều lần cho cùng một bài thi.
2. **Given** học viên có 0 lượt thi, **When** học viên cố gắng bắt đầu bài thi thử, **Then** hệ thống không cho phép bắt đầu bài thi.
3. **Given** số lượt thi của học viên, **When** hệ thống thực hiện thao tác trừ hoặc cộng lượt, **Then** số lượt không bao giờ nhỏ hơn 0 và không vượt quá 5.
4. **Given** học viên tạo bài thi thử, **When** hệ thống tạo đề, **Then** câu hỏi được lấy đúng kỹ năng, đúng Part, đúng số lượng, đúng thứ tự và tuân thủ quy tắc phân bổ của cấu trúc đề.
5. **Given** học viên bắt đầu bài thi, **When** hệ thống yêu cầu chuyển sang chế độ toàn màn hình, **Then** hệ thống hiển thị hướng dẫn, yêu cầu xác nhận và theo dõi trạng thái toàn màn hình trong quá trình làm bài.
6. **Given** học viên đang làm bài, **When** học viên thoát chế độ toàn màn hình hoặc rời khỏi trang/cửa sổ làm bài, **Then** hệ thống phát hiện, hiển thị cảnh báo và ghi nhận sự kiện vi phạm nếu hệ thống có lưu lịch sử sự kiện.
7. **Given** học viên thoát khỏi giao diện bài thi hoặc vi phạm quy định làm bài, **When** bài thi kết thúc, **Then** hệ thống **không tự động hoàn lại lượt thi** đã sử dụng.
8. **Given** học viên đang làm bài, **When** học viên cố gắng sao chép nội dung câu hỏi hoặc bài thi, **Then** hệ thống hạn chế thao tác sao chép trong phạm vi trình duyệt cho phép.
9. **Given** học viên trả lời bài thi, **When** hệ thống nhận câu trả lời, **Then** hệ thống hiển thị câu hỏi đúng thứ tự, lưu câu trả lời, quản lý trạng thái bài làm và cho phép nộp bài.
10. **Given** học viên nộp bài thi, **When** hệ thống chấm điểm, **Then** hệ thống chấm tự động cho các câu hỏi có thể chấm và lưu kết quả để phục vụ xem kết quả và thống kê.
11. **Given** học viên thực hiện bài kiểm tra đầu vào, **When** học viên hoàn thành, **Then** hệ thống lưu kết quả để phân loại hoặc xác định mức độ phù hợp của người học.

---

### User Story 5 - Xem lịch sử, thống kê và phân tích kết quả học tập (Priority: P2)

Học viên xem lịch sử bài làm (luyện tập và thi thử), thống kê kết quả theo kỹ năng, Part, bài làm và thời gian; phân tích bài làm đã hoàn thành; nhận gợi ý cải thiện và so sánh kết quả giữa các lần làm bài.

**Why this priority**: Có giá trị cao nhưng không chặn các chức năng chính. Chỉ có thể hoàn thiện đầy đủ khi dữ liệu bài làm đã được thu thập qua US1–US4.

**Independent Test**: Có thể kiểm thử độc lập bằng cách học viên hoàn thành một vài bài làm, sau đó truy cập lịch sử, thống kê và phân tích để xác nhận dữ liệu hiển thị đúng và có gợi ý cải thiện.

**Acceptance Scenarios**:

1. **Given** học viên đã hoàn thành các bài luyện tập và thi thử, **When** học viên xem lịch sử bài làm, **Then** hệ thống hiển thị danh sách bài làm đã hoàn thành kèm kết quả tương ứng.
2. **Given** học viên có dữ liệu kết quả, **When** học viên xem thống kê, **Then** hệ thống cung cấp thông tin thống kê theo kỹ năng, Part, bài làm và thời gian.
3. **Given** học viên chọn một bài làm đã hoàn thành, **When** học viên xem phân tích bài làm, **Then** hệ thống hiển thị điểm số, kết quả theo kỹ năng và Part, câu trả lời đúng/sai và các điểm yếu được xác định.
4. **Given** học viên có kết quả học tập, **When** hệ thống đưa ra gợi ý cải thiện, **Then** hệ thống giúp học viên xác định nội dung cần cải thiện.
5. **Given** học viên có nhiều lần làm bài, **When** học viên xem so sánh kết quả, **Then** hệ thống so sánh kết quả giữa các lần làm bài để theo dõi sự thay đổi trong quá trình học.

---

### Edge Cases

- Học viên đã có **5 lượt thi** và vẫn tiếp tục hoàn thành hoạt động học từ vựng: số lượt không được vượt quá 5, không cộng dồn.
- Học viên bắt đầu cùng một bài thi nhưng hệ thống gặp sự cố tạo đề: lượt thi không được trừ nhiều lần hoặc trừ khi bài thi chưa được tạo thành công.
- Học viên thoát toàn màn hình, chuyển tab/cửa sổ hoặc sao chép nội dung khi làm bài: hệ thống cảnh báo và ghi nhận sự kiện; lượt thi không được hoàn lại.
- Nhóm câu hỏi dùng chung một nội dung (hội thoại, bài nói, đoạn văn, tài liệu đọc): hệ thống không lấy từng câu hỏi độc lập nếu làm mất ngữ cảnh của nhóm.
- Học viên đăng ký bằng email đã tồn tại hoặc cố đăng nhập vào tài khoản bị khóa: hệ thống từ chối và thông báo rõ ràng.
- Quản trị viên nhập dữ liệu hàng loạt (từ vựng, câu hỏi) sai định dạng hoặc thiếu thông tin bắt buộc: hệ thống kiểm tra trước khi lưu và không đưa bản ghi không hợp lệ vào hệ thống.
- Câu hỏi được tạo ra nhưng không gắn đúng kỹ năng và Part: hệ thống không chấp nhận câu hỏi không tuân thủ cấu trúc đề thi.
- Học viên hết thời gian làm bài: hệ thống áp dụng cơ chế kiểm soát làm bài theo quy định và xử lý nộp bài theo trạng thái bài làm.
- Cấu trúc đề thi thay đổi trong tương lai: phải cập nhật tài liệu cấu trúc đề thi trước khi cập nhật hệ thống, không tự ý thay đổi cấu trúc.

## Requirements *(mandatory)*

Các yêu cầu dưới đây mô tả **hành vi hệ thống phải thực hiện**. Yêu cầu được nhóm theo lĩnh vực chức năng; mỗi yêu cầu phải kiểm thử được và thống nhất với `requirements.md`, `exam-structure.md` và `constitution.md`.

### Functional Requirements - Quản lý tài khoản người dùng

- **FR-001**: Hệ thống MUST cho phép người dùng đăng ký tài khoản với thông tin cần thiết; phải kiểm tra tính hợp lệ, kiểm tra tài khoản/email đã tồn tại, mã hóa mật khẩu trước khi lưu và khởi tạo số lượt thi theo quy định.
- **FR-002**: Hệ thống MUST cho phép người dùng đăng nhập bằng tài khoản đã đăng ký; phải xác thực mật khẩu, kiểm tra trạng thái tài khoản và từ chối nếu thông tin không hợp lệ hoặc tài khoản bị khóa.
- **FR-003**: Hệ thống MUST hỗ trợ người dùng đăng nhập bằng tài khoản Google và tạo hoặc liên kết tài khoản tương ứng khi phù hợp.
- **FR-004**: Hệ thống MUST cho phép học viên xem và cập nhật hồ sơ cá nhân (họ tên, thông tin tài khoản, chứng chỉ hoặc mục tiêu thi, thông tin cá nhân khác do hệ thống hỗ trợ).
- **FR-005**: Hệ thống MUST cung cấp chức năng hỗ trợ người dùng lấy lại quyền truy cập khi quên mật khẩu.
- **FR-006**: Hệ thống MUST cho phép người dùng đổi mật khẩu và phải kiểm tra thông tin cần thiết trước khi thực hiện.
- **FR-007**: Hệ thống MUST cung cấp chức năng nhắc nhở học tập cho người dùng theo chức năng được hỗ trợ.

### Functional Requirements - Quản lý nội dung cho quản trị viên

- **FR-010**: Hệ thống MUST cho phép quản trị viên thêm, xem, cập nhật và xóa từ vựng (từ, nghĩa, từ loại, phát âm, ví dụ tiếng Anh, ví dụ tiếng Việt).
- **FR-011**: Hệ thống MUST cho phép quản trị viên nhập nhiều từ vựng cùng lúc theo định dạng hệ thống quy định và kiểm tra dữ liệu trước khi lưu.
- **FR-012**: Hệ thống MUST cho phép quản trị viên thêm, xem, cập nhật và xóa câu hỏi; mỗi câu hỏi phải gắn đúng kỹ năng và Part theo cấu trúc đề thi quy định.
- **FR-013**: Hệ thống MUST cho phép quản trị viên nhập nhiều câu hỏi cùng lúc theo định dạng hệ thống quy định và kiểm tra dữ liệu trước khi đưa vào ngân hàng câu hỏi.
- **FR-014**: Hệ thống MUST cho phép quản trị viên quản lý cấu trúc đề và đề thi, và chỉ chấp nhận cấu trúc tuân thủ cấu trúc đề thi đã quy định.
- **FR-015**: Hệ thống MUST cho phép quản trị viên xem, tìm kiếm, lọc, khóa/chặn và xóa tài khoản người dùng.
- **FR-016**: Hệ thống MUST hỗ trợ quản trị viên tìm kiếm và lọc dữ liệu trong các khu vực quản lý phù hợp.
- **FR-017**: Hệ thống MUST chỉ cho phép tài khoản có quyền quản trị thực hiện các chức năng quản trị (phân quyền).

### Functional Requirements - Học từ vựng và luyện tập

- **FR-020**: Hệ thống MUST cho phép học viên xem danh sách từ vựng, lựa chọn chủ đề, xem các từ thuộc chủ đề và học từ vựng qua flashcard.
- **FR-021**: Hệ thống MUST lưu trạng thái học từ vựng của người dùng.
- **FR-022**: Hệ thống MUST hồi **1 lượt thi** khi người dùng hoàn thành hoạt động học từ vựng bằng flashcard theo điều kiện hồi lượt được xác định; số lượt không được vượt quá 5 và việc hồi lượt phải được kiểm soát để tránh cộng nhiều lượt cho cùng một lần học.
- **FR-023**: Hệ thống MUST cho phép học viên luyện Listening theo cấu trúc quy định (Part 1: 6, Part 2: 25, Part 3: 39, Part 4: 30) và lựa chọn Part phù hợp.
- **FR-024**: Hệ thống MUST cho phép học viên luyện Reading theo cấu trúc quy định (Part 5: 30, Part 6: 16, Part 7: 54) và lựa chọn Part phù hợp.
- **FR-025**: Hệ thống MUST cho phép học viên luyện Speaking theo 5 Part (câu 1–2, 3–4, 5–7, 8–10, 11) và lưu câu trả lời dạng âm thanh để phục vụ chấm điểm và phân tích kết quả.
- **FR-026**: Hệ thống MUST cho phép học viên luyện Writing theo 3 Part (câu 1–5, 6–7, 8) và lưu câu trả lời dạng văn bản để phục vụ chấm điểm và phân tích kết quả.
- **FR-027**: Hệ thống MUST cho phép học viên lựa chọn kỹ năng, Part và phạm vi luyện tập; khi luyện theo Part, câu hỏi phải lấy đúng từ Part đó.
- **FR-028**: Hệ thống MUST giữ nguyên mối quan hệ giữa nội dung chung (hội thoại, bài nói, đoạn văn, tài liệu đọc) và các câu hỏi liên quan; không lấy từng câu hỏi độc lập nếu làm mất ngữ cảnh của nhóm câu hỏi.

### Functional Requirements - Thi thử, kiểm tra đầu vào và kiểm soát làm bài

- **FR-030**: Hệ thống MUST cấp cho mỗi người dùng tối đa **5 lượt thi**, hiển thị số lượt hiện tại, không cho phép bắt đầu bài thi khi số lượt bằng 0, trừ **1 lượt** khi bắt đầu bài thi thử hợp lệ và không cho phép số lượt nhỏ hơn 0 hoặc vượt quá 5.
- **FR-031**: Hệ thống MUST trừ lượt một cách nhất quán để tránh trừ nhiều lần cho cùng một bài thi hoặc tạo bài thi mà không cập nhật lượt.
- **FR-032**: Hệ thống MUST không tự động hoàn lại lượt thi khi người dùng thoát khỏi giao diện bài thi, chuyển tab/cửa sổ hoặc vi phạm quy định làm bài.
- **FR-033**: Hệ thống MUST cho phép tạo bài thi dựa trên cấu trúc đề đã xác định cho toàn bộ bài thi hoặc một kỹ năng; việc lựa chọn câu hỏi phải đúng kỹ năng, đúng Part, đúng số lượng, đúng thứ tự và tuân thủ quy tắc của cấu trúc đề.
- **FR-034**: Hệ thống MUST cho phép học viên thi thử toàn bài theo cấu trúc quy định và thi thử theo từng kỹ năng được hệ thống hỗ trợ.
- **FR-035**: Hệ thống MUST cung cấp bài kiểm tra đầu vào để đánh giá trình độ ban đầu và lưu kết quả để phân loại hoặc xác định mức độ phù hợp của người học.
- **FR-036**: Hệ thống MUST yêu cầu chuyển sang chế độ toàn màn hình khi bắt đầu bài thi, hiển thị hướng dẫn, yêu cầu xác nhận và theo dõi trạng thái toàn màn hình trong quá trình làm bài.
- **FR-037**: Hệ thống MUST phát hiện việc thoát toàn màn hình, hiển thị cảnh báo và ghi nhận sự kiện vi phạm nếu hệ thống có lưu lịch sử sự kiện.
- **FR-038**: Hệ thống MUST phát hiện các trường hợp người dùng rời khỏi trang làm bài hoặc chuyển sang ứng dụng/tab khác trong phạm vi trình duyệt cho phép và hiển thị cảnh báo.
- **FR-039**: Hệ thống MUST hạn chế thao tác sao chép nội dung câu hỏi và bài thi (bằng chuột hoặc bàn phím) trong phạm vi có thể kiểm soát bằng trình duyệt.
- **FR-040**: Hệ thống MUST ghi nhận các sự kiện liên quan đến việc rời khỏi chế độ làm bài để phục vụ cảnh báo, theo dõi và kiểm tra lịch sử vi phạm.
- **FR-041**: Trong quá trình làm bài, hệ thống MUST hiển thị câu hỏi theo đúng thứ tự, cho phép trả lời theo loại câu hỏi, lưu câu trả lời, quản lý trạng thái bài làm và cho phép nộp bài.
- **FR-042**: Hệ thống MUST chấm điểm sau khi học viên hoàn thành bài thi đối với các câu hỏi có thể chấm tự động; câu trả lời Speaking và Writing được xử lý theo cơ chế chấm điểm hệ thống hỗ trợ; kết quả phải được lưu.

### Functional Requirements - Thống kê và lịch sử bài làm

- **FR-050**: Hệ thống MUST cho phép học viên xem lịch sử bài luyện tập và bài thi thử đã hoàn thành kèm kết quả tương ứng.
- **FR-051**: Hệ thống MUST cung cấp thông tin thống kê kết quả học tập và làm bài theo kỹ năng, Part, bài làm và thời gian.
- **FR-052**: Hệ thống MUST cho phép học viên xem và phân tích kết quả bài làm đã hoàn thành (điểm số, kết quả theo kỹ năng và Part, câu trả lời đúng/sai, điểm yếu được xác định).
- **FR-053**: Hệ thống MUST cung cấp gợi ý cải thiện dựa trên kết quả học tập và làm bài.
- **FR-054**: Hệ thống MUST hỗ trợ so sánh kết quả giữa các lần làm bài để theo dõi sự thay đổi trong quá trình học tập.

### Functional Requirements - Cấu trúc đề thi và dữ liệu

- **FR-060**: Hệ thống MUST tuân thủ cấu trúc đề thi TOEIC đã thống nhất và không tự ý thêm, bỏ hoặc thay đổi các Part trong đề thi.
- **FR-061**: Hệ thống MUST phân biệt rõ kỹ năng và Part; mỗi Part phải thuộc đúng kỹ năng tương ứng và không được đánh đồng Part giữa các kỹ năng.
- **FR-062**: Mỗi câu hỏi MUST được xác định rõ kỹ năng, Part, dạng câu hỏi và hình thức trả lời phù hợp.
- **FR-063**: Khi lấy câu hỏi ngẫu nhiên để tạo bài luyện hoặc đề thi, hệ thống MUST giới hạn phạm vi ngẫu nhiên trong cấu trúc đã xác định và đảm bảo không làm mất ngữ cảnh của nhóm câu hỏi dùng chung nội dung.
- **FR-064**: Hệ thống MUST lưu câu trả lời và kết quả của người học để phục vụ xem lại lịch sử, tính điểm, thống kê, phân tích và gợi ý cải thiện.
- **FR-065**: Hệ thống MUST đảm bảo các chức năng cá nhân của học viên được giới hạn trong phạm vi tài khoản tương ứng.

### Key Entities *(include if feature involves data)*

Các thực thể chính được xác định từ yêu cầu nghiệp vụ và cấu trúc đề thi. Danh sách này mô tả khái niệm và quan hệ nghiệp vụ, không mô tả chi tiết triển khai.

- **User (Học viên / Quản trị viên)**: Tài khoản người dùng của hệ thống; mang thông tin cá nhân, vai trò (học viên hoặc quản trị viên), trạng thái tài khoản và số lượt thi hiện tại (tối đa 5).
- **Vocabulary (Từ vựng)**: Từ vựng với các thông tin từ, nghĩa, từ loại, phát âm, ví dụ tiếng Anh và ví dụ tiếng Việt; có thể thuộc một chủ đề.
- **Question (Câu hỏi)**: Câu hỏi trong ngân hàng câu hỏi; phải gắn đúng kỹ năng và Part, xác định dạng câu hỏi và hình thức trả lời.
- **Skill (Kỹ năng)**: Xác định một trong bốn kỹ năng: `LISTENING`, `READING`, `SPEAKING`, `WRITING`.
- **ExamSection (Part)**: Xác định từng Part thuộc một kỹ năng (ví dụ Listening gồm L1→Part 1 ... L4→Part 4; Reading gồm R5→Part 5 ... R7→Part 7; Speaking gồm S1→Part 1 ... S5→Part 5; Writing gồm W1→Part 1 ... W3→Part 3).
- **TestBlueprint (Mẫu cấu trúc đề)**: Lưu một mẫu cấu trúc đề thi (ví dụ TOEIC Full Test, TOEIC Listening, TOEIC Reading, TOEIC Speaking, TOEIC Writing).
- **BlueprintRule**: Quy định cách phân bổ câu hỏi trong một cấu trúc đề, bao gồm Part, loại câu hỏi, độ khó (nếu có yêu cầu), số lượng câu cần lấy và thứ tự.
- **TestForm (Đề thi)**: Lưu một đề thi cụ thể được tạo dựa trên `TestBlueprint`.
- **TestFormQuestion**: Xác định các câu hỏi thực tế thuộc một đề và thứ tự xuất hiện của chúng.
- **Answer / Submission (Câu trả lời / bài làm)**: Câu trả lời và bài làm của người học được lưu để phục vụ chấm điểm, xem lịch sử, thống kê, phân tích và gợi ý cải thiện.
- **TestAttempt (Lượt thi)**: Số lượt thi của mỗi người dùng; tối đa 5, trừ khi bắt đầu bài thi thử hợp lệ và hồi qua hoạt động học từ vựng bằng flashcard.
- **LearningProgress (Tiến độ học tập)**: Trạng thái học từ vựng và tiến độ học tập của người dùng được lưu để phục vụ nhắc nhở, theo dõi và thống kê.

## Success Criteria *(mandatory)*

Các tiêu chí thành công mang tính đo lường được, không phụ thuộc công nghệ, thể hiện kết quả từ góc nhìn người dùng.

### Measurable Outcomes

- **SC-001**: 100% tài khoản mới được khởi tạo với số lượt thi đúng quy định (tối đa 5) ngay sau khi đăng ký thành công.
- **SC-002**: 100% bài thi thử hợp lệ được trừ đúng **1 lượt thi**; không có trường hợp số lượt nhỏ hơn 0 hoặc vượt quá 5 trong mọi tình huống.
- **SC-003**: 100% bài luyện tập và đề thi thử được tạo đúng kỹ năng, đúng Part, đúng số lượng và đúng thứ tự câu hỏi theo cấu trúc đề thi đã quy định.
- **SC-004**: 100% nhóm câu hỏi dùng chung nội dung được giữ nguyên mối quan hệ, không xuất hiện tình huống mất ngữ cảnh khi lấy câu hỏi.
- **SC-005**: 100% thao tác thoát toàn màn hình, chuyển tab/cửa sổ hoặc sao chép trong khi làm bài được phát hiện, cảnh báo và ghi nhận sự kiện (nếu hệ thống lưu lịch sử sự kiện).
- **SC-006**: 100% bài thi đã nộp được chấm điểm tự động cho các câu hỏi có thể chấm và kết quả được lưu để xem lại.
- **SC-007**: 100% câu trả lời và kết quả của người học được lưu và có thể truy xuất trong lịch sử, thống kê và phân tích bài làm.
- **SC-008**: Học viên có thể xem thống kê và so sánh kết quả giữa các lần làm bài để theo dõi sự thay đổi trong quá trình học.
- **SC-009**: 100% chức năng quản trị chỉ truy cập được bởi tài khoản có quyền quản trị; 100% chức năng cá nhân bị giới hạn trong phạm vi tài khoản tương ứng.
- **SC-010**: 100% hoạt động học từ vựng bằng flashcard hoàn thành đúng điều kiện hồi lượt được cộng đúng **1 lượt** và không cộng nhiều lần cho cùng một lần học.

## Assumptions

- Hệ thống chỉ hỗ trợ các kỹ năng và cấu trúc đề thi được quy định trong `exam-structure.md` (TOEIC Listening & Reading, TOEIC Speaking, TOEIC Writing); không mở rộng sang dạng bài khác trong phạm vi tài liệu hiện tại.
- Số lượt thi tối đa là **5** và được hồi thông qua hoạt động học từ vựng bằng flashcard; đây là quy định bắt buộc của hệ thống.
- Câu trả lời Speaking được thực hiện dưới dạng âm thanh và câu trả lời Writing dưới dạng văn bản; cả hai đều được lưu để phục vụ chấm điểm và phân tích kết quả.
- Việc chấm điểm Speaking và Writing sử dụng cơ chế chấm điểm được hệ thống hỗ trợ; các câu hỏi trắc nghiệm (Listening và Reading) được chấm tự động.
- Các chức năng mở rộng (trò chơi hoặc hình thức học từ vựng mở rộng, nhắc nhở học tập, đăng nhập Google...) chỉ được triển khai khi nằm trong phạm vi được thống nhất của dự án.
- Dữ liệu bài làm và kết quả được lưu vĩnh viễn để phục vụ lịch sử, thống kê và phân tích trong suốt quá trình học của học viên.
- Cấu trúc đề thi được quản lý bằng các khái niệm `Skill`, `ExamSection`, `TestBlueprint`, `BlueprintRule`, `TestForm` và `TestFormQuestion`; khi cấu trúc đề thi thay đổi trong tương lai, tài liệu `exam-structure.md` phải được cập nhật trước khi cập nhật hệ thống.
- Hệ thống phải tuân thủ các quy tắc bảo mật: mật khẩu không lưu dạng văn bản thuần, thông tin nhạy cảm và cấu hình được lưu trong biến môi trường khi phù hợp, không đưa bí mật vào mã nguồn và repository.
- Mọi thay đổi mã nguồn được quản lý bằng Git; chức năng được phát triển theo nhiệm vụ đã phân công và tuân theo tài liệu đặc tả đã thống nhất.

---

*Tài liệu này nhất quán với `constitution.md`, `exam-structure.md` và `requirements.md`. Khi có thay đổi lớn về yêu cầu, cấu trúc đề thi hoặc cơ sở dữ liệu, tài liệu này phải được xem xét và cập nhật cùng với các tài liệu liên quan.*








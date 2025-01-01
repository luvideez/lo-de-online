<!DOCTYPE html>
<html>
<head>
<title>Dự đoán Lô - Đề online</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  body {
    font-family: 'Arial', sans-serif;
    background-color: #f8f9fa;
    margin: 0;
    padding: 0;
    color: #333;
  }

  .container {
    width: 90%;
    max-width: 600px;
    margin: 30px auto;
    background-color: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
  }

  h2 {
    text-align: center;
    color: #007bff;
    margin-bottom: 25px;
  }

  label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
  }

  input[type="number"],
  input[type="date"] {
    width: calc(100% - 22px);
    padding: 12px;
    margin-bottom: 20px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 16px;
  }

  input[type="submit"] {
    display: block;
    width: 100%;
    padding: 12px 20px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 18px;
    transition: background-color 0.3s ease;
    margin-top: 20px;
  }

  input[type="submit"]:hover {
    background-color: #218838;
  }

  .result-container {
    margin-top: 30px;
  }

  h3 {
    color: #dc3545;
    margin-bottom: 15px;
  }

  .typing-text {
    font-size: 18px;
    line-height: 1.7;
    white-space: pre-wrap; /* Preserve line breaks */
    overflow: hidden; /* Hide overflowing text during animation */
    border-right: .15em solid orange; /* Cursor effect */
    margin: 0;
  }

  @keyframes typing {
    from { width: 0; }
    to { width: 100%; }
  }

  @keyframes blink-caret {
    from, to { border-color: transparent; }
    50% { border-color: orange; }
  }

  .typing-text.animate {
    animation: typing 3.5s steps(40, end), blink-caret .75s step-end infinite;
  }

  /* Responsive */
  @media (max-width: 576px) {
    .container {
      padding: 20px;
    }

    input[type="number"],
    input[type="date"] {
      font-size: 14px;
    }

    .typing-text {
      font-size: 16px;
    }
  }
</style>
</head>
<body>

<div class="container">
  <h2>Dự đoán lô - đề online</h2>

  <form method="post">
    <label for="date">Ngày:</label>
    <input type="date" id="date" name="date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>

    <label for="number">Số may mắn (khác 0):</label>
    <input type="number" id="number" name="number" min="1" required>

    <input type="submit" value="Dự đoán">
  </form>

  <div class="result-container">
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $selectedDate = $_POST["date"];
      $number = $_POST["number"];

      // Lấy ngày hiện tại
      $today = date("Y-m-d");
      $currentTime = date("H:i:s");

      // Xác định ngày áp dụng tính toán
      if ($selectedDate == $today) {
        if ($currentTime < "17:00:00") {
          $dateToUse = $today;
        } else {
          $dateToUse = date('Y-m-d', strtotime('+1 day'));
        }
      } else {
        $dateToUse = $selectedDate;
      }

      // Chuyển định dạng ngày sang ddmmyyyy
      $date = date("dmY", strtotime($dateToUse));

      // Tính toán kết quả
      $result = ($date * $number * 142857) / (365.25 * pi());
      $luckyNumber = round($result);
      $luckyNumber = substr($luckyNumber, -2);

      // Chuyển đổi định dạng ngày sang dd-mm-yyyy
      $dateToUseFormatted = date("d-m-Y", strtotime($dateToUse));

      // Tính toán tỷ lệ chính xác
      $dataUrl = "https://raw.githubusercontent.com/khiemdoan/vietnam-lottery-xsmb-analysis/refs/heads/main/data/xsmb-2-digits.json";
      $jsonData = file_get_contents($dataUrl);
      $data = json_decode($jsonData, true);

      $count = 0;
      $total = 0;

      foreach ($data as $entry) {
        $total++;
        $dailyMatches = 0;

        foreach ($entry as $key => $value) {
          if ($key !== "date") {
            $formattedValue = (strlen($value) == 1) ? "0" . $value : $value;
            if ($formattedValue == $luckyNumber) {
              $dailyMatches++;
            }
          }
        }

        if ($dailyMatches > 0) {
          $count++;
        }
      }

      $accuracy = ($total > 0) ? ($count / $total) * 100 : 0;

      // Hiển thị kết quả
      echo "<h3>Kết quả:</h3>";
      echo "<p class='typing-text' id='result-date'></p>";
      echo "<p class='typing-text' id='result-number'></p>";

      echo "<h3>Tỷ lệ chính xác (theo ngày):</h3>";
      echo "<p class='typing-text' id='result-accuracy'></p>";

      // JavaScript để tạo hiệu ứng type chữ
      echo "<script>
              function typeWriter(element, text, i = 0) {
                if (i < text.length) {
                  element.innerHTML += text.charAt(i);
                  i++;
                  setTimeout(() => typeWriter(element, text, i), 20);
                } else {
                  element.classList.remove('animate'); // Remove animation class when done
                }
              }

              // Lấy nội dung kết quả từ PHP
              const dateText = 'Ngày áp dụng: " . $dateToUseFormatted . "';
              const numberText = 'Số may mắn: " . $luckyNumber . "';
              const accuracyText = 'Trên tổng số " . $total . " ngày, số " . $luckyNumber . " xuất hiện ít nhất một lần trong " . $count . " ngày. Tỷ lệ chính xác của thuật toán dựa trên con số may mắn của bạn: " . number_format($accuracy, 2) . "%';

              // Lấy các phần tử HTML
              const dateElement = document.getElementById('result-date');
              const numberElement = document.getElementById('result-number');
              const accuracyElement = document.getElementById('result-accuracy');

              // Thêm class 'animate' để kích hoạt hiệu ứng
              dateElement.classList.add('animate');
              numberElement.classList.add('animate');
              accuracyElement.classList.add('animate');

              // Bắt đầu hiệu ứng type chữ
              typeWriter(dateElement, dateText);
              setTimeout(() => typeWriter(numberElement, numberText), dateText.length * 50 + 500); // Delay for number
              setTimeout(() => typeWriter(accuracyElement, accuracyText), (dateText.length + numberText.length) * 50 + 1000); // Delay for accuracy
            </script>";
    }
    ?>
  </div>
</div>

</body>
</html>

<!DOCTYPE html>
<html>
<head>
<title>Lô đề online</title>
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

  .button-container2 {
    display: flex;
    justify-content: space-around;
    align-items: center;
    margin-bottom: 0px;
  }

  .btn {
    padding: 12px 20px;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 18px;
    transition: background-color 0.3s ease;
    margin: 5px; /* Add some space between buttons */
  }

  .btn-primary {
    background-color: #28a745;
  }

  .btn-primary:hover {
    background-color: #218838;
  }

  .btn-secondary {
    background-color: #6c757d;
  }

  .btn-secondary:hover {
    background-color: #5a6268;
  }

  .result-container {
    margin-top: 30px;
  }

  h3 {
    color: #dc3545;
    margin-bottom: 15px;
  }

  /* Style for the emphasized results */
  .result-highlight {
    font-size: 36px; /* Double the size */
    font-weight: bold; /* Bold */
    color: red; /* Red color */
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

    .btn {
      font-size: 14px;
      padding: 10px 16px;
    }

    .result-highlight {
      font-size: 28px; /* Smaller size on smaller screens */
    }
  }
</style>
</head>
<body>

<div class="container">
  <h2>Dự đoán lô đề online</h2>

  <form method="post">
    <label for="date">Ngày kiểm tra:</label>
    <input type="date" id="date" name="date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>

    <label for="number">Số may mắn (khác 0):</label>
    <input type="number" id="number" name="number" min="1" required>

    <div class="button-container2">
      <button type="submit" class="btn btn-primary">Dự đoán</button>
      <button type="button" id="resetButton" class="btn btn-secondary">Dự đoán lại</button>
    </div>
  </form>

  <div class="result-container"></div>

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

    // Lấy dữ liệu kết quả xổ số từ file JSON
    $dataUrl = "https://raw.githubusercontent.com/khiemdoan/vietnam-lottery-xsmb-analysis/refs/heads/main/data/xsmb-2-digits.json";
    $jsonData = file_get_contents($dataUrl);
    $data = json_decode($jsonData, true);

    // Tính toán tỷ lệ chính xác từ trước đến nay
    $successfulPredictions = 0;
    $totalDays = 0;

    foreach ($data as $entry) {
      $totalDays++;
      $historicalDate = date("dmY", strtotime($entry['date']));
      $historicalResult = ($historicalDate * $number * 142857) / (365.25 * pi());
      $historicalLuckyNumber = round($historicalResult);
      $historicalLuckyNumber = substr($historicalLuckyNumber, -2);
      $match = false;
      foreach ($entry as $prize => $numbers) {
          if ($prize !== 'date') {
              if (!is_array($numbers)) {
                  $numbers = [$numbers];
              }
              foreach ($numbers as $number_item) {
                  $formattedNumber = (strlen($number_item) == 1) ? "0" . $number_item : $number_item;
                  if ($formattedNumber == $historicalLuckyNumber) {
                      $match = true;
                      break;
                  }
              }
          }
      }
      if ($match) {
          $successfulPredictions++;
      }
    }

    $accuracyAllTime = ($totalDays > 0) ? ($successfulPredictions / $totalDays) * 100 : 0;

    // Hiển thị kết quả
    echo "
    <div class='result-container'>
        <h3>Kết quả:</h3>
        <p>Ngày áp dụng: " . $dateToUseFormatted . "</p>
        <p><span class='label'>Số may mắn: </span><span class='result-highlight'>" . $luckyNumber . "</span></p>
        <h3>Tỷ lệ chính xác (từ trước đến nay):</h3>
        <p><span class='label'>Dựa trên số may mắn đã chọn và áp dụng công thức tính cho tất cả các ngày trong lịch sử, tỷ lệ dự đoán chính xác là: </span><span class='result-highlight'>" . number_format($accuracyAllTime, 2) . "%</span></p>
        <h3>Con số may mắn mà bạn đã chọn là: ".$number."</h3>
    </div>
    ";
  }
  ?>

</div>

<script>
  const resetButton = document.getElementById("resetButton");
  const resultContainer = document.querySelector(".result-container");
  const dateInput = document.getElementById("date");
  const numberInput = document.getElementById("number");

  resetButton.addEventListener("click", () => {
    resultContainer.innerHTML = ""; // Xóa nội dung của result-container
    dateInput.value = ""; // Reset giá trị của date input
    numberInput.value = ""; // Reset giá trị của number input
  });
</script>
<br>
      <center>  <a href="https://tiktok.com/@ditucogivui">Kênh tiktok - Đi tù có gì vui?</a/> </center><br><br>
</body>
</html>

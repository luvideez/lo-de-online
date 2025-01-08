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
  margin-bottom: 20px;
}

.btn {
  padding: 12px 20px;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 18px;
  transition: background-color 0.3s ease;
  margin: 5px;
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

.result-highlight {
  font-size: 36px;
  font-weight: bold;
  color: red;
}

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
    font-size: 28px;
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

    <label for="number">Số may mắn (4-9 chữ số):</label>
    <input type="number" id="number" name="number" min="1000" max="999999999" required>

    <div class="button-container2">
      <button type="submit" class="btn btn-primary">Dự đoán</button>
      <button type="button" id="resetButton" class="btn btn-secondary">Dự đoán lại</button>
    </div>
  </form>

  <div class="result-container"></div>

  <?php
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $startTime = microtime(true);

    $selectedDate = $_POST["date"];
    $number = $_POST["number"];

    $today = date("Y-m-d");
    $currentTime = date("H:i:s");

    $dateToUse = ($selectedDate == $today && $currentTime < "17:00:00") ? $today : date('Y-m-d', strtotime('+1 day', strtotime($selectedDate)));

    $date = date("dmY", strtotime($dateToUse));
    $dd = date("d", strtotime($dateToUse));
    $mm = date("m", strtotime($dateToUse));
    $yyyy = date("Y", strtotime($dateToUse));

    $result = ($date * $dd + $number);
    $luckyNumber = round($result);

    $firstTwoDigits = (int)substr($luckyNumber, 0, 2);
    $lastTwoDigits = (int)substr($luckyNumber, -2);

    $product = $firstTwoDigits * $lastTwoDigits;
    $average = substr($product, -2);
    $average = str_pad($average, 2, '0', STR_PAD_LEFT);

    $dateToUseFormatted = date("d-m-Y", strtotime($dateToUse));

    $dataUrl = "https://raw.githubusercontent.com/khiemdoan/vietnam-lottery-xsmb-analysis/refs/heads/main/data/xsmb-2-digits.json";
    $jsonData = file_get_contents($dataUrl);
    $data = json_decode($jsonData, true);

    $successfulPredictions = 0;
    $totalDays = 0;
    $successfulDatesAndNumbers = [];

    foreach ($data as $entry) {
      $totalDays++;
      $historicalDate = date("dmY", strtotime($entry['date']));
      $historicaldd = date("d", strtotime($entry['date']));
      $historicalmm = date("m", strtotime($entry['date']));
      $historicalyyyy = date("Y", strtotime($entry['date']));

      $historicalResult = ($historicalDate * ($historicaldd + $historicalmm)) + $number;
      $historicalLuckyNumber = round($historicalResult);

      $historicalFirstTwoDigits = (int)substr($historicalLuckyNumber, 0, 2);
      $historicalLastTwoDigits = (int)substr($historicalLuckyNumber, -2);

      $historicalProduct = $historicalFirstTwoDigits + $historicalLastTwoDigits;
      $historicalAverage = substr($historicalProduct, -2);
      $historicalAverage = str_pad($historicalAverage, 2, "0", STR_PAD_LEFT);

      $match = false;
      foreach ($entry as $prize => $numbers) {
        if ($prize !== 'date') {
          $numbers = (array)$numbers;
          foreach ($numbers as $number_item) {
            $formattedNumber = str_pad($number_item, 2, "0", STR_PAD_LEFT);
            if ($formattedNumber == $historicalAverage) {
              $match = true;
              break 2;
            }
          }
        }
      }

      if ($match) {
        $successfulPredictions++;
        $successfulDatesAndNumbers[] = [
          'date' => date("d-m-Y", strtotime($entry['date'])),
          'number' => $historicalAverage
        ];
      }
    }

    $accuracyAllTime = ($totalDays > 0) ? ($successfulPredictions / $totalDays) * 100 : 0;

    // Tính toán khoảng cách ngày gần nhất và xa nhất
    $nearestDays = PHP_INT_MAX;
    $farthestDays = 0;
    $nearestDates = "";
    $farthestDates = "";

    if (count($successfulDatesAndNumbers) > 1) {
      $successfulDatesTimestamps = array_map(function ($item) {
        return strtotime($item['date']);
      }, $successfulDatesAndNumbers);
      sort($successfulDatesTimestamps);

      for ($i = 1; $i < count($successfulDatesTimestamps); $i++) {
        $diff = $successfulDatesTimestamps[$i] - $successfulDatesTimestamps[$i - 1];
        $days = round($diff / (60 * 60 * 24));

        if ($days < $nearestDays) {
          $nearestDays = $days;
          $nearestDates = date('d-m-Y', $successfulDatesTimestamps[$i - 1]) . ' và ' . date('d-m-Y', $successfulDatesTimestamps[$i]);
        }
        if ($days > $farthestDays) {
          $farthestDays = $days;
          $farthestDates = date('d-m-Y', $successfulDatesTimestamps[$i - 1]) . ' và ' . date('d-m-Y', $successfulDatesTimestamps[$i]);
        }
      }
    }

    // Lưu kết quả vào file ketqua.txt
    $results = [];
    if (file_exists('ketqua.txt')) {
      $results = json_decode(file_get_contents('ketqua.txt'), true);
    }
    $results[] = ['number' => $number, 'accuracy' => $accuracyAllTime];

    usort($results, function ($a, $b) {
      return $b['accuracy'] - $a['accuracy'];
    });

    $results = array_slice($results, 0, 10);
    file_put_contents('ketqua.txt', json_encode($results));

    $endTime = microtime(true);
    $executionTime = ($endTime - $startTime);

    echo "<div class='result-container'>";
    echo "<p>Thời gian xử lý: " . number_format($executionTime, 4) . " giây</p>";
    echo "<h3>Kết quả:</h3>";
    echo "<p>Ngày áp dụng: " . $dateToUseFormatted . "</p>";
    echo "<p><span class='label'>Lô dự đoán: </span><span class='result-highlight'>" . $average . "</span></p>";
    echo "<h3>Tỷ lệ chính xác (từ trước đến nay):</h3>";
    echo "<p><span class='label'>Dựa trên số may mắn đã chọn và áp dụng công thức tính cho tất cả các ngày trong lịch sử, tỷ lệ dự đoán chính xác là: </span><span class='result-highlight'>" . number_format($accuracyAllTime, 2) . "%</span></p>";

    if (count($successfulDatesAndNumbers) > 1) {
      echo "<p>Khoảng cách gần nhất giữa 2 ngày dự đoán đúng: " . $nearestDays . " ngày (" . $nearestDates . ")</p>";
      echo "<p>Khoảng cách xa nhất giữa 2 ngày dự đoán đúng: " . $farthestDays . " ngày (" . $farthestDates . ")</p>";
    }

    echo "<h3>Con số may mắn mà bạn đã chọn là: " . $number . "</h3>";

    if (!empty($successfulDatesAndNumbers)) {
      echo "<h3>Các ngày dự đoán chính xác:</h3>";
      echo "<ul>";
      foreach ($successfulDatesAndNumbers as $item) {
        echo "<li>" . $item['date'] . " - Kết quả: " . $item['number'] . "</li>";
      }
      echo "</ul>";
    }

    echo "<h3>10 kết quả có tỷ lệ chính xác cao nhất:</h3>";
    echo "<ul>";
    foreach ($results as $result) {
      echo "<li>Số may mắn: " . $result['number'] . " - Tỷ lệ chính xác: " . number_format($result['accuracy'], 2) . "%</li>";
    }
    echo "</ul>";
    echo "</div>";
  }
  ?>

</div>

<script>
  const resetButton = document.getElementById("resetButton");
  const resultContainer = document.querySelector(".result-container");
  const dateInput = document.getElementById("date");
  const numberInput = document.getElementById("number");
  const form = document.querySelector('form');

  resetButton.addEventListener("click", () => {
    resultContainer.innerHTML = "";
    dateInput.value = "";
    numberInput.value = "";
  });

  form.addEventListener('submit', (event) => {
    const numberValue = numberInput.value;
    if (numberValue.length < 4 || numberValue.length > 9) {
      alert('Vui lòng nhập số may mắn từ 4 đến 9 chữ số.');
      event.preventDefault();
    }
  });
</script>
<br>
<center><a href="https://tiktok.com/@ditucogivui">Kênh tiktok - Đi tù có gì vui?</a></center><br><br>
</body>
</html>

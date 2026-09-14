const readline = require("readline");

const rl = readline.createInterface({
  input: process.stdin,

  output: process.stdout,
});

// รับชื่อ

rl.question("ป้อนชื่อนักเรียน: ", (name) => {
  // รับคะแนนระหว่างภาค

  rl.question("ป้อนคะแนนระหว่างภาค (0-60): ", (mid) => {
    // รับคะแนนปลายภาค

    rl.question("ป้อนคะแนนปลายภาค (0-40): ", (final) => {
      // แปลงข้อมูลเป็นตัวเลข

      mid = Number(mid);

      final = Number(final); // คำนวณคะแนนรวม

      const total = mid + final; // คำนวณเกรด

      let grade;

      if (total >= 80) {
        grade = "A";
      } else if (total >= 70) {
        grade = "B";
      } else if (total >= 60) {
        grade = "C";
      } else if (total >= 50) {
        grade = "D";
      } else {
        grade = "F";
      } // แสดงผล

      console.log("\n==============================");

      console.log("       ผลการเรียน");

      console.log("==============================");

      console.log("ชื่อ       :", name);

      console.log("กลางภาค   :", mid);

      console.log("ปลายภาค   :", final);

      console.log("คะแนนรวม  :", total);

      console.log("Grade      :", grade);

      console.log("==============================");

      rl.close();
    });
  });
});

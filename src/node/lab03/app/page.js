import Link from "next/link";

const programs = [
  {
    id: "01",
    title: "โปรแกรมคำนวณเกรด (Grade)",
    description: "คำนวณคะแนนระหว่างภาคและปลายภาค ตัดเกรด A ถึง F ตามเกณฑ์มาตรฐาน",
    href: "/grade",
  },
  {
    id: "02",
    title: "โปรแกรมคำนวณส่วนลดสินค้า",
    description: "คำนวณอัตราส่วนลดตามขั้นราคา สรุปยอดส่วนลดและจำนวนเงินสุทธิที่ต้องชำระ",
    href: "/discount",
  },
  {
    id: "03",
    title: "โปรแกรมแม่สูตรคูณ",
    description: "สร้างตารางสูตรคูณตั้งแต่ลำดับที่ 1 ถึง 12 ตามแม่ตัวเลขที่ระบุ",
    href: "/multiplication",
  },
  {
    id: "04",
    title: "โปรแกรมคำนวณดัชนีมวลกาย (BMI)",
    description: "คำนวณค่าดัชนีมวลกายจากน้ำหนักและส่วนสูง พร้อมประเมินเกณฑ์ภาวะสุขภาพ",
    href: "/bmi",
  },
];

export default function HomePage() {
  return (
    <main className="w-full max-w-xl mx-auto my-auto py-6">
      {/* Header */}
      <header className="mb-8 text-center sm:text-left">
        <div className="inline-flex items-center gap-2 px-2.5 py-1 rounded-md bg-zinc-100 border border-zinc-200 text-xs font-medium text-zinc-600 mb-3">
          <span>Next.js</span>
          <span className="text-zinc-300">•</span>
          <span>Lab 03</span>
        </div>
        <h1 className="text-2xl sm:text-3xl font-bold text-zinc-900 tracking-tight">
          ระบบคำนวณและประมวลผล
        </h1>
        <p className="text-sm text-zinc-500 mt-1.5">
          เลือกโปรแกรมที่ต้องการใช้งานเพื่อเริ่มต้นคำนวณข้อมูล
        </p>
      </header>

      {/* Tool Cards List */}
      <div className="space-y-3">
        {programs.map((program) => (
          <Link
            key={program.id}
            href={program.href}
            className="group flex items-start gap-4 p-4 rounded-xl bg-white border border-zinc-200 hover:border-zinc-300 hover:shadow-sm transition-all"
          >
            <div className="flex-shrink-0 w-9 h-9 rounded-lg bg-zinc-100 text-zinc-700 flex items-center justify-center font-semibold text-sm group-hover:bg-zinc-900 group-hover:text-white transition-colors">
              {program.id}
            </div>
            <div className="flex-1 min-w-0">
              <div className="flex items-center justify-between">
                <h2 className="text-base font-semibold text-zinc-900 group-hover:text-zinc-700 transition-colors">
                  {program.title}
                </h2>
                <span className="text-zinc-400 group-hover:text-zinc-700 transition-transform group-hover:translate-x-0.5">
                  &rarr;
                </span>
              </div>
              <p className="text-xs text-zinc-500 mt-1">{program.description}</p>
            </div>
          </Link>
        ))}
      </div>

      {/* Footer Note */}
      <footer className="mt-8 text-center text-xs text-zinc-400">
        Next.js Laboratory &bull; Clean Edition
      </footer>
    </main>
  );
}

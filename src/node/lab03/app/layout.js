import { Prompt } from "next/font/google";
import "./globals.css";

const prompt = Prompt({
  weight: ["300", "400", "500", "600", "700"],
  subsets: ["thai", "latin"],
  variable: "--font-prompt",
  display: "swap",
});

export const metadata = {
  title: "ระบบคำนวณและประมวลผล - Lab 03",
  description: "ระบบคำนวณและประมวลผล พัฒนาด้วย Next.js (Lab 03)",
};

export default function RootLayout({ children }) {
  return (
    <html lang="th" className={`${prompt.variable} h-full antialiased`}>
      <body className="min-h-screen bg-zinc-50 text-zinc-800 flex flex-col justify-between p-4 sm:p-8">
        {children}
      </body>
    </html>
  );
}

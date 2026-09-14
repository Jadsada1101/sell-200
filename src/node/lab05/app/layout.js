import { Prompt } from "next/font/google";
import Navbar from "@/components/Navbar";
import "./globals.css";

const promptFont = Prompt({
  weight: ["300", "400", "500", "600"],
  subsets: ["thai", "latin"],
  variable: "--font-prompt",
  display: "swap",
});

export const metadata = {
  title: "ระบบบริหารจัดการข้อมูลนักศึกษา",
  description: "ระบบบริหารจัดการข้อมูลนักศึกษาและสาขาวิชา",
};

export default function RootLayout({ children }) {
  return (
    <html lang="th" className={`${promptFont.variable} h-full`}>
      <body className="min-h-full flex flex-col bg-zinc-50 text-zinc-900 antialiased font-sans">
        <Navbar />
        <main className="flex-1 flex flex-col">{children}</main>
      </body>
    </html>
  );
}

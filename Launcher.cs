using System;
using System.Drawing;
using System.Windows.Forms;
using System.Diagnostics;
using System.IO;

namespace ELibraryLauncher
{
    public class MainForm : Form
    {
        private Button btnStart;
        private Button btnStop;
        private Label lblStatus;
        private Label lblTitle;
        private Label lblHint;
        private string serviceName = "ELibraryService";
        private string nssmPath = "nssm.exe";

        public MainForm()
        {
            // Setup Form
            this.Text = "مدير تشغيل المكتبة الإلكترونية";
            this.Size = new Size(450, 260);
            this.StartPosition = FormStartPosition.CenterScreen;
            this.FormBorderStyle = FormBorderStyle.FixedDialog;
            this.MaximizeBox = false;
            this.RightToLeft = RightToLeft.Yes;
            this.RightToLeftLayout = true;
            this.BackColor = Color.White;
            this.Font = new Font("Segoe UI", 10F, FontStyle.Regular, GraphicsUnit.Point, ((byte)(0)));

            // Title
            lblTitle = new Label();
            lblTitle.Text = "لوحة تحكم سيرفر المكتبة";
            lblTitle.Font = new Font("Segoe UI", 16F, FontStyle.Bold);
            lblTitle.ForeColor = Color.FromArgb(6, 78, 59); // Emerald
            lblTitle.AutoSize = true;
            lblTitle.Location = new Point(100, 20);
            this.Controls.Add(lblTitle);

            // Status Label
            lblStatus = new Label();
            lblStatus.Text = "جاهز لتلقي الأوامر";
            lblStatus.AutoSize = true;
            lblStatus.Font = new Font("Segoe UI", 10F, FontStyle.Bold);
            lblStatus.Location = new Point(140, 65);
            lblStatus.ForeColor = Color.Gray;
            this.Controls.Add(lblStatus);

            // Start Button
            btnStart = new Button();
            btnStart.Text = "▶ تشغيل السيرفر";
            btnStart.Size = new Size(160, 50);
            btnStart.Location = new Point(30, 110);
            btnStart.BackColor = Color.FromArgb(16, 185, 129); // Green
            btnStart.ForeColor = Color.White;
            btnStart.FlatStyle = FlatStyle.Flat;
            btnStart.Font = new Font("Segoe UI", 11F, FontStyle.Bold);
            btnStart.Cursor = Cursors.Hand;
            btnStart.Click += new EventHandler(BtnStart_Click);
            this.Controls.Add(btnStart);

            // Stop Button
            btnStop = new Button();
            btnStop.Text = "⏹ إيقاف السيرفر";
            btnStop.Size = new Size(160, 50);
            btnStop.Location = new Point(230, 110);
            btnStop.BackColor = Color.FromArgb(239, 68, 68); // Red
            btnStop.ForeColor = Color.White;
            btnStop.FlatStyle = FlatStyle.Flat;
            btnStop.Font = new Font("Segoe UI", 11F, FontStyle.Bold);
            btnStop.Cursor = Cursors.Hand;
            btnStop.Click += new EventHandler(BtnStop_Click);
            this.Controls.Add(btnStop);
            
            // Hint Label
            lblHint = new Label();
            lblHint.Text = "ملاحظة: يجب أن يكون ملف nssm.exe متواجداً في نفس المجلد";
            lblHint.AutoSize = true;
            lblHint.Font = new Font("Segoe UI", 8F);
            lblHint.Location = new Point(50, 180);
            lblHint.ForeColor = Color.DarkGray;
            this.Controls.Add(lblHint);
        }

        private void RunNssmCommand(string arguments)
        {
            try
            {
                if (!File.Exists(nssmPath))
                {
                    MessageBox.Show("لم يتم العثور على أداة nssm.exe!\nيرجى وضعها بجانب هذا البرنامج.", "ملف مفقود", MessageBoxButtons.OK, MessageBoxIcon.Error);
                    return;
                }

                ProcessStartInfo psi = new ProcessStartInfo();
                psi.FileName = nssmPath;
                psi.Arguments = arguments;
                psi.CreateNoWindow = true;
                psi.WindowStyle = ProcessWindowStyle.Hidden;
                psi.UseShellExecute = true;
                // يطلب صلاحيات المسؤول إجبارياً لإدارة الخدمات
                psi.Verb = "runas"; 

                using (Process process = Process.Start(psi))
                {
                    process.WaitForExit();
                    if (process.ExitCode == 0)
                    {
                        MessageBox.Show("تم تنفيذ الأمر بنجاح!", "نجاح", MessageBoxButtons.OK, MessageBoxIcon.Information);
                    }
                    else
                    {
                        MessageBox.Show("تعذر التنفيذ! تأكد من أن الخدمة (ELibraryService) تم تثبيتها مسبقاً.", "تحذير", MessageBoxButtons.OK, MessageBoxIcon.Warning);
                    }
                }
            }
            catch (System.ComponentModel.Win32Exception)
            {
                // User cancelled the UAC prompt
                MessageBox.Show("يجب الموافقة على صلاحيات المسؤول (Administrator) لتتمكن من التحكم بالسيرفر.", "تم الإلغاء", MessageBoxButtons.OK, MessageBoxIcon.Warning);
            }
            catch (Exception ex)
            {
                MessageBox.Show("حدث خطأ غير متوقع:\n" + ex.Message, "خطأ", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
        }

        private void BtnStart_Click(object sender, EventArgs e)
        {
            lblStatus.Text = "جاري التشغيل...";
            lblStatus.ForeColor = Color.Orange;
            this.Update();
            RunNssmCommand("start " + serviceName);
            lblStatus.Text = "السيرفر يعمل الآن";
            lblStatus.ForeColor = Color.Green;
        }

        private void BtnStop_Click(object sender, EventArgs e)
        {
            lblStatus.Text = "جاري الإيقاف...";
            lblStatus.ForeColor = Color.Orange;
            this.Update();
            RunNssmCommand("stop " + serviceName);
            lblStatus.Text = "السيرفر متوقف";
            lblStatus.ForeColor = Color.Red;
        }

        [STAThread]
        static void Main()
        {
            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);
            Application.Run(new MainForm());
        }
    }
}

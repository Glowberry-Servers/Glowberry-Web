using System.Diagnostics;
using System.Runtime.InteropServices;
using glowberry.webserver;

namespace glowberry
{
    internal class Program
    {
        /// <summary>
        /// The specific type of delegate required by the SetConsoleCtrlHandler windows function.
        /// </summary>
        private delegate bool ConsoleEventDelegate(int eventType);
        
        /// <summary>
        /// Adds or removes an application-defined HandlerRoutine function from the list of handler functions for the calling process. <br/>
        /// Provides a similar notification for console application and services that 'WM_QUERYENDSESSION' provides for graphical applications
        /// </summary>
        /// <param name="callback">The function to be run when the console application closes</param>
        /// <param name="add">If this parameter is TRUE, the handler is added; if it is FALSE, the handler is removed.</param>
        /// <returns></returns>
        [DllImport("kernel32.dll", SetLastError = true)]
        private static extern bool SetConsoleCtrlHandler(ConsoleEventDelegate callback, bool add);

        /// <summary>
        /// The delegate that will be called when the console is closed.
        /// </summary>
        private static ConsoleEventDelegate ExitHandler { get; } = ConsoleEventCallback;

        /// <summary>
        /// Kills the nginx process when the console. To be used in a 
        /// </summary>
        /// <param name="eventType"></param>
        /// <returns></returns>
        static bool ConsoleEventCallback(int eventType)
        {
            Process.Start("cmd", "/c taskkill /IM nginx.exe /F");
            return false;
        }

        /// <summary>
        /// Grab the singleton instance of the GlowberryWebServer class and run it.
        /// </summary>
        public static void Main()
        {
            SetConsoleCtrlHandler(ExitHandler, true);
            GlowberryWebServer.Instance.Run();
        }
    }
}
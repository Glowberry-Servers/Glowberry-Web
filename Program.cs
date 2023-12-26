using glowberry.web.server;

namespace glowberry
{
    internal class Program
    {
        /// <summary>
        /// Grab the singleton instance of the GlowberryWebServer class and run it.
        /// </summary>
        public static void Main() => GlowberryWebServer.Instance.Run();
    }
}
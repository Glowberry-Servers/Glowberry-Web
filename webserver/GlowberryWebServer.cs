using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.IO;
using System.Linq;
using System.Net;
using System.Reflection;
using System.Threading.Tasks;
using glowberry.api;
using glowberry.common;
using glowberry.attributes;
using glowberry.common.factories;
using glowberry.utils;
using glowberry.webserver.extensions;
using LaminariaCore_Winforms.common;

namespace glowberry.webserver
{
    /// <summary>
    /// This singleton class is responsible for handling all web requests by managing an http listener.
    /// </summary>
    public partial class GlowberryWebServer
    {
        
        /// <summary>
        /// The singleton instance of the GlowberryWebServer class.
        /// </summary>
        public static GlowberryWebServer Instance { get; } = new GlowberryWebServer();
        
        /// <summary>
        /// The HttpListener object that will be used to listen for incoming requests.
        /// </summary>
        private HttpListener Listener { get; set; }
        
        /// <summary>
        /// The factory object that will be used to get all of the server type mappings.
        /// </summary>
        private ServerTypeMappingsFactory MappingsFactory { get; } = new ServerTypeMappingsFactory();
        
        /// <summary>
        /// The section of the filesystem that contains all of the servers.
        /// </summary>
        private Section ServersSection { get; }

        /// <summary>
        /// Make the constructor for the GlowberryWebServer class; Singleton enforced. <br/>
        /// Creates the HttpListener object and adds the api prefix to it.
        /// </summary>
        private GlowberryWebServer()
        {
            this.Listener = new HttpListener();
            this.Listener.Prefixes.Add("http://localhost:34556/api/");
            
            string appDataPath = Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData);
            string glowberryPath = Path.Combine(appDataPath, ".Glowberry");
            
            this.ServersSection = new FileManager(glowberryPath).AddSection("servers");
        }

        /// <summary>
        /// Prepares the web server by updating the server versions cache and performing all the checks
        /// provided by the API to ensure that the web server will provide the information as expected.
        /// </summary>
        /// <returns>Whether the preparation was successful</returns>
        private async Task<bool> Prepare()
        {
            try
            {
                // Tries to update the cache files for all of the server types.
                foreach (string serverType in this.MappingsFactory.GetSupportedServerTypes())
                    await ResourceLoader.UpdateCacheFileForServerType(serverType, this.MappingsFactory);

                return true;
            }
            catch (Exception e)
            {
                // If any error occurs, log it and return false.
                Logging.Logger.Fatal("A fatal error occured during initialisation and the server had to exit: " + e);
                return false;
            }
        }

        /// <summary>
        /// Runs the web server by starting the HttpListener and then listening for incoming requests.
        /// </summary>
        public void Run()
        {
            Logging.Logger.Info("Initialising the Glowberry Web Server...");
            if (!this.Prepare().Result) return;
            
            try
            {
                this.Listener.Start();
                Logging.Logger.Info($@"Started the Glowberry Web Server for {this.Listener.Prefixes.First()}");

                // Starts the main running loop waiting for requests to come in from the client.
                while (this.Listener.IsListening)
                {
                    HttpListenerContext context = this.Listener.GetContext();
                    string endpointRequested = context.Request?.Url.LocalPath;

                    Logging.Logger.Info($@"Received a request from {context.Request?.RemoteEndPoint?.Address} - {endpointRequested}");
                    HttpListenerResponse response = this.TryExecuteEndpointMethod(endpointRequested, context);

                    Logging.Logger.Info($"Sent a response with status code {response.StatusCode}, size {response.ContentLength64} of type {response.ContentType} back to {context.Request?.RemoteEndPoint?.Address}");
                    context.Response.Close();
                }
            }

            // If any error occurs, log it and throw it.
            catch (Exception e)
            {
                Logging.Logger.Fatal(e);
                throw;
            }

            // Stops the listener if it is still running by the end of everything.
            finally { this.Listener.Stop(); }
        }

        /// <summary>
        /// Tries to figure out which type of request was made and calls it; If none is found, returns a simple
        /// dictionary response with an error message.
        /// </summary>
        /// <param name="endpoint">The endpoint name to look for</param>
        /// <param name="context">The context to pass into the endpoint method</param>
        /// <returns>The response returned by the method</returns>
        private HttpListenerResponse TryExecuteEndpointMethod(string endpoint, HttpListenerContext context)
        {
            try
            {
                MethodInfo method = this.GetType().GetMethods(BindingFlags.NonPublic | BindingFlags.Instance)
                    .Where(x => x.CustomAttributes.Any(y => y.AttributeType == typeof(Endpoint)))
                    .FirstOrDefault(x => endpoint.Contains(x.GetCustomAttribute<Endpoint>()?.Name ?? throw new InvalidOperationException()));
                
                // If the method is null, then the endpoint doesn't exist
                if (method == null || context.Request == null) return EndpointError(context);
            
                // If the method is not null, then the endpoint exists, so we can call it.
                return (HttpListenerResponse) method.Invoke(this, new object[] {context});
                
            }
            catch (Exception e) { return EndpointError(context); }   
            
        }
        
        /// <summary>
        /// Returns a standard error response with a 404 status code.
        /// </summary>
        /// <param name="context">The HTTP Context to get all of the information from</param>
        /// <returns>The response to send back to the client</returns>
        private static HttpListenerResponse EndpointError(HttpListenerContext context)
        {
            HttpListenerResponse response = context.Response;
            response.StatusCode = 404;
            response.ContentType = "application/json";
            response.ContentEncoding = System.Text.Encoding.UTF8;
            
            // Create a JSON body with the error message and write it into the response.
            Dictionary<string, dynamic> body = new Dictionary<string, dynamic>
            {
                {"error", $"{context.Request.Url.LocalPath} is not a valid endpoint."}
            };
            
            response.WriteJson(body);
            return response;
        }
        
        /// <summary>
        /// Returns an empty dictionary to be used as a response body in json.
        /// </summary>
        private static Dictionary<string, dynamic> GetEmptyResponseJson() => new Dictionary<string, dynamic>();
    }
}
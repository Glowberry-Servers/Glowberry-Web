using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Reflection;
using glowberry.common;
using glowberry.attributes;
using glowberry.webserver.extensions;

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
        /// Make the constructor for the GlowberryWebServer class; Singleton enforced. <br/>
        /// Creates the HttpListener object and adds the api prefix to it.
        /// </summary>
        private GlowberryWebServer()
        {
            this.Listener = new HttpListener();
            this.Listener.Prefixes.Add("http://localhost:34556/api/");
        }

        /// <summary>
        /// Runs the web server by starting the HttpListener and then listening for incoming requests.
        /// </summary>
        public void Run()
        {
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

                    Logging.Logger.Info($"Send a response with status code {response.StatusCode}, size {response.ContentLength64} of type {response.ContentType} back to {context.Request?.RemoteEndPoint?.Address}");
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
            MethodInfo method = this.GetType().GetMethods(BindingFlags.NonPublic | BindingFlags.Default)
                .FirstOrDefault(x => x.GetCustomAttribute<Endpoint>().Name == endpoint);
            
            // If the method is null, then the endpoint doesn't exist.
            if (method == null || context.Request == null) return EndpointError(context);
            
            // If the method is not null, then the endpoint exists, so we can call it.
            return (HttpListenerResponse) method.Invoke(this, new object[] {context});
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
    }
}
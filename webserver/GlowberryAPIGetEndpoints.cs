using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Net;
using glowberry.api.server;
using glowberry.attributes;
using glowberry.webserver.extensions;

namespace glowberry.webserver
{
    /// <summary>
    /// This part of the GlowberryWebServer class is responsible for registering all of the GET
    /// endpoints and any methods useful to them.
    /// </summary>
    public partial class GlowberryWebServer
    {
        
        /// <summary>
        /// Ensures that the specified request is a GET request, and if not, returns
        /// false, setting the response object to the appropriate status code and description.
        /// </summary>
        /// <param name="request">The request to be checked</param>
        /// <param name="response">The response to be sent and handled</param>
        /// <returns>Whether the request was a get request</returns>
        private static bool EnsureGetRequest(HttpListenerRequest request, HttpListenerResponse response)
        {
            if (request.HttpMethod != "GET")
            {
                response.StatusCode = 405;
                response.StatusDescription = "Method Not Allowed: Try sending a GET request instead?";
                return false;
            }

            return true;
        }
        
        /// <summary>
        /// Gets a list of installed java version paths  on the system.
        /// </summary>
        private List<string> GetInstalledJavaVersions()
        {
            // Defines the paths to the java installation directories.
            string programFilesJavaPath = Path.Combine(Environment.ExpandEnvironmentVariables("%ProgramW6432%"), "Java");
            string programFilesX86JavaPath = Path.Combine(Environment.ExpandEnvironmentVariables("%ProgramFiles(x86)%"), "Java");
            
            List<string> javaInstallations = new ();
            
            // Checks if the directories exist, and if they do, adds them to the list of java installations.
            if (Directory.Exists(programFilesJavaPath))
                javaInstallations.AddRange(Directory.GetDirectories(programFilesJavaPath).ToArray());

            if (Directory.Exists(programFilesX86JavaPath))
                javaInstallations.AddRange(Directory.GetDirectories(programFilesX86JavaPath).ToArray());
            
            return javaInstallations;
        }
        
        /// <summary>
        /// Handles the ping endpoint, which is used to check if the web server is online. Simply
        /// return a 200 OK response.
        /// </summary>
        /// <returns>The response object to be handled by the server afterwards</returns>
        [Endpoint("/api/ping")]
        private HttpListenerResponse PingWebServer(HttpListenerContext context)
        {
            if (!EnsureGetRequest(context.Request, context.Response)) return context.Response;
            HttpListenerResponse response = context.Response;
            
            response.StatusCode = 200;
            response.StatusDescription = "OK";
            return response;
        }
        
        /// <summary>
        /// Returns the server list, which is a list of all of the servers that are currently
        /// registered with the web server.
        /// </summary>
        [Endpoint("/api/server/list")]
        private HttpListenerResponse GetServerList(HttpListenerContext context)
        {
            if (!EnsureGetRequest(context.Request, context.Response)) return context.Response;
            HttpListenerResponse response = context.Response;
            
            var json = GetEmptyResponseJson();
            json.Add("servers", new List<string>());

            foreach (var serverName in ServerInteractions.GetServerList())
                json["servers"].Add(serverName);

            response.StatusCode = 200;
            response.ContentType = "application/json";
            response.ContentEncoding = System.Text.Encoding.UTF8;
            response.WriteJson(json);
            return response;
        }

        /// <summary>
        /// Returns a list of all of the supported server types.
        /// </summary>
        [Endpoint("/api/server/types")]
        private HttpListenerResponse GetSupportedServerTypes(HttpListenerContext context)
        {
            if (!EnsureGetRequest(context.Request, context.Response)) return context.Response;
            HttpListenerResponse response = context.Response;
            
            var json = GetEmptyResponseJson();
            json.Add("types", new List<string>());
            
            foreach (var serverType in this.MappingsFactory.GetSupportedServerTypes())
                json["types"].Add(serverType);
            
            response.StatusCode = 200;
            response.ContentType = "application/json";
            response.ContentEncoding = System.Text.Encoding.UTF8;
            response.WriteJson(json);
            return response;
        }

        /// <summary>
        /// Returns a list of all of the supported server versions for the specified server type.
        /// </summary>
        [Endpoint("/api/server/versions")]
        private HttpListenerResponse GetServerVersionsFor(HttpListenerContext context)
        {
            if (!EnsureGetRequest(context.Request, context.Response)) return context.Response;
            HttpListenerResponse response = context.Response;
            
            var json = GetEmptyResponseJson();
            
            // If the server type is not specified, return an error.
            if (!context.Request.QueryString.AllKeys.Contains("type"))
            {
                json.Add("error", "No server type specified.");
                response.StatusCode = 400;
                response.ContentType = "application/json";
                response.ContentEncoding = System.Text.Encoding.UTF8;
                response.WriteJson(json);
                return response;
            }
            
            // If the server type is specified, get the versions for it.
            string serverType = context.Request.QueryString["type"];
            json.Add("type-versions", new List<string>());

            foreach (var serverVersion in this.MappingsFactory.GetCacheContentsForType(serverType))
                json["type-versions"].Add(serverVersion.Key);
            
            response.StatusCode = 200;
            response.ContentType = "application/json";
            response.ContentEncoding = System.Text.Encoding.UTF8;
            response.WriteJson(json);
            return response;
        }

        /// <summary>
        /// Returns a list of all of the installed java versions on the system.
        /// </summary>
        [Endpoint("/api/java-versions")]
        private HttpListenerResponse GetInstalledJavaVersions(HttpListenerContext context)
        {
            if (!EnsureGetRequest(context.Request, context.Response)) return context.Response;
            HttpListenerResponse response = context.Response;
            
            var json = GetEmptyResponseJson();
            json.Add("java-versions", new List<string>());

            foreach (var javaVersion in this.GetInstalledJavaVersions())
                json["java-versions"].Add(javaVersion);
            
            response.StatusCode = 200;
            response.ContentType = "application/json";
            response.ContentEncoding = System.Text.Encoding.UTF8;
            response.WriteJson(json);
            return response;
        }
        
        /// <summary>
        /// Checks if the specified server is currently building or not.
        /// </summary>
        [Endpoint("/api/check/build")]
        private HttpListenerResponse CheckBuildState(HttpListenerContext context)
        {
            if (!EnsureGetRequest(context.Request, context.Response)) return context.Response;
            HttpListenerResponse response = context.Response;
            
            var json = GetEmptyResponseJson();

            // If the server id is not specified, return an error.
            if (!context.Request.QueryString.AllKeys.Contains("server_id"))
            {
                json.Add("error", "No server ID specified.");
                response.StatusCode = 400;
                response.ContentType = "application/json";
                response.ContentEncoding = System.Text.Encoding.UTF8;
                response.WriteJson(json);
                return response;
            }
            
            // Prepare the response for the server state.
            string serverId = context.Request.QueryString["server_id"];
            
            response.StatusCode = 200;
            response.ContentType = "application/json";
            response.ContentEncoding = System.Text.Encoding.UTF8;
            
            // If the server directory doesn't exist, then the build failed.
            if (this.ServersSection.GetFirstSectionNamed(serverId) == null)
            {
                json.Add("state", "failed");
                response.WriteJson(json);
                return response;
            }
            
            // If build.lock file exists, then the build is still in progress.
            if (this.ServersSection.GetFirstSectionNamed(serverId).GetFirstDocumentNamed("build.lock") != null)
            {
                json.Add("state", "building");
                response.WriteJson(json);
                return response;
            }
            
            // If the server directory exists and the build.lock file doesn't, then the build succeeded.
            json.Add("state", "success");
            response.WriteJson(json);
            return response;
        }

        /// <summary>
        /// Returns the console output for the specified server limited to the specified amount of lines.
        /// </summary>
        [Endpoint("/api/server/output")]
        private HttpListenerResponse GetConsoleOutput(HttpListenerContext context)
        {
            if (!EnsureGetRequest(context.Request, context.Response)) return context.Response;
            HttpListenerResponse response = context.Response;
            
            var json = GetEmptyResponseJson();
            
            // If the server id is not specified, return an error.
            if (!context.Request.QueryString.AllKeys.Contains("server_id"))
            {
                json.Add("error", "No server ID specified.");
                response.StatusCode = 400;
                response.ContentType = "application/json";
                response.ContentEncoding = System.Text.Encoding.UTF8;
                response.WriteJson(json);
                return response;
            }
            
            // Gets the server id and the lines 
            string serverId = context.Request.QueryString["server_id"];
            int lines = 10;  // If the amount of lines is not specified, assume 10.

            if (!context.Request.QueryString.AllKeys.Contains("lines"))
                lines = int.Parse(context.Request.QueryString["lines"]);
            
            // Starts an instance of the server interactions api and gets the output buffer.
            List<string> outputBuffer = new ServerAPI().Interactions(serverId).GetOutputBuffer();
            
            // Extracts the last x lines from the output buffer and returns them.
            json.Add("output", outputBuffer.Take(lines).ToList());
            json.Add("length", lines);
            
            response.StatusCode = 200;
            response.ContentType = "application/json";
            response.ContentEncoding = System.Text.Encoding.UTF8;
            response.WriteJson(json);
            return response;
        }

        /// <summary>
        /// Returns the server information for the specified server using the editing api.
        /// </summary>
        [Endpoint("/api/server/info")]
        private HttpListenerResponse GetServerInfo(HttpListenerContext context)
        {
            if (!EnsureGetRequest(context.Request, context.Response)) return context.Response;
            HttpListenerResponse response = context.Response;
            
            var json = GetEmptyResponseJson();
            
            // If the server id is not specified, return an error.
            if (!context.Request.QueryString.AllKeys.Contains("server_id"))
            {
                json.Add("error", "No server ID specified.");
                response.StatusCode = 400;
                response.ContentType = "application/json";
                response.ContentEncoding = System.Text.Encoding.UTF8;
                response.WriteJson(json);
                return response;
            }
            
            // Gets the server id and starts an instance of the server editing api.
            string serverId = context.Request.QueryString["server_id"];
            ServerEditing editingApi = new ServerAPI().Editor(serverId);
            
            response.StatusCode = 200;
            response.ContentType = "application/json";
            response.ContentEncoding = System.Text.Encoding.UTF8;
            response.WriteJson(editingApi.GetServerInformation().ToDictionary());
            return response;
        }
    }
}
using System.Collections.Generic;
using System.Net;
using System.Threading.Tasks;
using glowberry.api.server;
using glowberry.attributes;
using glowberry.common.handlers;
using glowberry.webserver.extensions;

namespace glowberry.webserver
{
    /// <summary>
    /// This part of the GlowberryWebServer class is responsible for registering all of the POST
    /// endpoints and any methods useful to them.
    /// </summary>
    public partial class GlowberryWebServer
    {

        /// <summary>
        /// Ensures that the specified request is a POST request, and if not, returns false, setting
        /// the response object to the appropriate status code and description.
        /// </summary>
        /// <param name="request">The request to be checked</param>
        /// <param name="response">The response to be sent and handled</param>
        /// <returns>Whether the request is POST or not</returns>
        private static bool EnsurePostRequest(HttpListenerRequest request, HttpListenerResponse response)
        {
            if (request.HttpMethod != "POST")
            {
                response.StatusCode = 405;
                response.StatusDescription = "Method Not Allowed: Try sending a POST request instead?";
                return false;
            }

            return true;
        }

        /// <summary>
        /// Starts building a server with the specified information in a fire-and-forget manner.
        /// </summary>
        [Endpoint("/api/server/build")]
        private HttpListenerResponse BuildServer(HttpListenerContext context)
        {
            if (!EnsurePostRequest(context.Request, context.Response)) return context.Response;
            HttpListenerResponse response = context.Response;

            Dictionary<string, string> body = context.Request.GetPostData();

            // Check if the required parameters are present
            if (!body.ContainsKey("server_id") || !body.ContainsKey("type") || !body.ContainsKey("version") ||
                !body.ContainsKey("java"))
            {
                response.StatusCode = 400;
                response.StatusDescription = "Bad Request: Missing required parameters";
                return response;
            }

            // Initialises the ServerAPI class and starts building the server
            ServerBuilding builder = new ServerAPI().Builder(body["server_id"], body["type"], body["version"]);
            Task.Run(() => builder.Run(new MessageProcessingOutputHandler(null), body["java"]));

            response.StatusCode = 200;
            response.StatusDescription = "OK";
            return response;
        }

        /// <summary>
        /// Starts a server with the specified information in a fire-and-forget manner.
        /// </summary>
        [Endpoint("/api/server/start")]
        private HttpListenerResponse StartServer(HttpListenerContext context)
        {
            if (!EnsurePostRequest(context.Request, context.Response)) return context.Response;
            HttpListenerResponse response = context.Response;

            Dictionary<string, string> body = context.Request.GetPostData();

            // Check if the required parameters are present
            if (!body.ContainsKey("server_id"))
            {
                response.StatusCode = 400;
                response.StatusDescription = "Bad Request: Missing required parameters";
                return response;
            }
            
            // Checks if the server is already running
            ServerInteractions interactions = new ServerAPI().Interactions(body["server_id"]);
            
            if (interactions.IsRunning())
            {
                response.StatusCode = 400;
                response.StatusDescription = "Bad Request: Server is already running";
                return response;
            }

            // Edits the server properties to disallow running with a GUI
            ServerEditing editor = new ServerAPI().Editor(body["server_id"]);
            var info = editor.GetServerInformation();
            info.UseGUI = false;
            editor.UpdateServerSettings(info.ToDictionary());

            // Initialises the ServerAPI class and starts building the server
            ServerStarting starter = new ServerAPI().Starter(body["server_id"]);
            Task.Run(() => starter.Run());

            response.StatusCode = 200;
            response.StatusDescription = "OK";
            return response;
        }

        /// <summary>
        /// Sends a message to the server with the specified information in a fire-and-forget manner.
        /// </summary>
        [Endpoint("/api/server/send-message")]
        private HttpListenerResponse SendMessage(HttpListenerContext context)
        {
            if (!EnsurePostRequest(context.Request, context.Response)) return context.Response;
            HttpListenerResponse response = context.Response;

            Dictionary<string, string> body = context.Request.GetPostData();

            // Check if the required parameters are present
            if (!body.ContainsKey("server_id") || !body.ContainsKey("message"))
            {
                response.StatusCode = 400;
                response.StatusDescription = "Bad Request: Missing required parameters";
                return response;
            }

            // Initialises the interactions api and sends the message
            ServerInteractions interactions = new ServerAPI().Interactions(body["server_id"]);
            interactions.WriteToServerStdin(body["message"]);

            response.StatusCode = 200;
            response.StatusDescription = "OK";
            return response;
        }

        /// <summary>
        /// Kills the server with the specified information in a fire-and-forget manner.
        /// </summary>
        [Endpoint("/api/server/kill")]
        private HttpListenerResponse KillServer(HttpListenerContext context)
        {
            if (!EnsurePostRequest(context.Request, context.Response)) return context.Response;
            HttpListenerResponse response = context.Response;

            Dictionary<string, string> body = context.Request.GetPostData();

            // Check if the required parameters are present
            if (!body.ContainsKey("server_id"))
            {
                response.StatusCode = 400;
                response.StatusDescription = "Bad Request: Missing required parameters";
                return response;
            }

            // Initialises the interactions api and kills the server
            ServerInteractions interactions = new ServerAPI().Interactions(body["server_id"]);
            interactions.KillServerProcess();

            response.StatusCode = 200;
            response.StatusDescription = "OK";
            return response;
        }

        /// <summary>
        /// Edits the server with the specified information in a fire-and-forget manner using
        /// the ServerEditing API.
        /// </summary>
        [Endpoint("/api/server/edit")]
        private HttpListenerResponse EditServer(HttpListenerContext context)
        {
            if (!EnsurePostRequest(context.Request, context.Response)) return context.Response;
            HttpListenerResponse response = context.Response;

            Dictionary<string, string> body = context.Request.GetPostData();

            // Check if the required parameters are present
            if (!body.ContainsKey("server_id") || !body.ContainsKey("property") || !body.ContainsKey("value"))
            {
                response.StatusCode = 400;
                response.StatusDescription = "Bad Request: Missing required parameters";
                return response;
            }

            // Updates the server settings with the new values provided
            ServerEditing editor = new ServerAPI().Editor(body["server_id"]);
            editor.UpdateServerSettings(body);

            response.StatusCode = 200;
            response.StatusDescription = "OK";
            return response;
        }
    }
}
using System.Collections.Generic;
using System.IO;
using System.Net;

namespace glowberry.webserver.extensions;

/// <summary>
/// This class implements extension methods for the HttpListenerRequest class to simplify operations mainly
/// with POST requests.
/// </summary>
public static class HttpListenerRequestExtensions
{
    
    /// <summary>
    /// Opens the input stream of the request and reads it into a string, then splits it into a dictionary
    /// for easy access to the POST data.
    /// </summary>
    /// <returns>A dictionary of key-value pairs of the POST data</returns>
    public static Dictionary<string, string> GetPostData(this HttpListenerRequest request)
    {
        // Get the request body and convert it into a string
        string requestBody = new StreamReader(request.InputStream).ReadToEnd();
        
        // Split the request body into a dictionary of key-value pairs
        Dictionary<string, string> postData = new ();
        foreach (string pair in requestBody.Split('&'))
        {
            string[] keyValue = pair.Split('=');
            postData.Add(keyValue[0], keyValue[1]);
        }

        return postData;
    }
    
}
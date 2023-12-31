﻿using System.Collections.Generic;
using System.Net;

namespace glowberry.webserver.extensions
{
    /// <summary>
    /// This class implements extension methods for the HttpListenerResponse class to simplify
    /// operations on the response object.
    /// </summary>
    public static class HttpListenerResponseExtensions
    {
        
        /// <summary>
        /// Automatically writes a JSON content body into the response object, handling the buffers,
        /// content type, length, and everything else.
        /// </summary>
        /// <param name="response">The response object to apply the writing into</param>
        /// <param name="body">A dictionary to be converted into JSON with everything to be transmitted.</param>
        public static void WriteJson(this HttpListenerResponse response, Dictionary<string, dynamic> body)
        {
            // Convert the body into a JSON string and then into a byte array.
            string serializedResponse = Newtonsoft.Json.JsonConvert.SerializeObject(body);
            byte[] buffer = System.Text.Encoding.UTF8.GetBytes(serializedResponse);
            
            // Set the content type and length of the response object
            response.ContentType = "application/json";
            response.ContentLength64 = buffer.Length;
            
            // Write the buffer into the response object and close the stream
            response.OutputStream.Write(buffer, 0, buffer.Length);
            response.OutputStream.Close();
        }

        /// <summary>
        /// Overloaded method for writing a JSON body into the response object, but with a dictionary of strings.
        /// </summary>
        /// <param name="response">The response object to apply the writing into</param>
        /// <param name="body">A dictionary to be converted into JSON with everything to be transmitted.</param>
        public static void WriteJson(this HttpListenerResponse response, Dictionary<string, string> body)
        {
            // Converts the dictionary into a dictionary of dynamic objects and calls the other method.
            Dictionary<string, dynamic> dynamicBody = new Dictionary<string, dynamic>();
            foreach (KeyValuePair<string, string> pair in body) dynamicBody.Add(pair.Key, pair.Value);
            response.WriteJson(dynamicBody);
        }
        
    }
}
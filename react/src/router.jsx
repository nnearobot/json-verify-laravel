import {createBrowserRouter, Navigate} from "react-router-dom";
import DefaultLayout from "./components/DefaultLayout";
import GuestLayout from "./components/GuestLayout";
import Signin from "./views/Signin";
import NotFound from "./views/NotFound";
import Signup from "./views/Signup";
import Results from "./views/Results";
import UploadForm from "./views/UploadForm";

const router = createBrowserRouter([
  {
    path: '/',
    element: <DefaultLayout/>,
    children: [
      {
        path: '/',
        element: <Navigate to="/upload"/>
      },
      {
        path: '/upload',
        element: <UploadForm/>
      },
      {
        path: '/results',
        element: <Results/>
      }
    ]
  },
  {
    path: '/',
    element: <GuestLayout/>,
    children: [
      {
        path: '/signin',
        element: <Signin/>
      },
      {
        path: '/signup',
        element: <Signup/>
      }
    ]
  },
  {
    path: "*",
    element: <NotFound/>
  }
])

export default router;
